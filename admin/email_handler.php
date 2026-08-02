<?php
/**
 * Admin Email Campaign Handler
 * Sends bulk emails to: (a) registered but not submitted, (b) all submitters
 */

// Must be called from admin context
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// --- Auth guard ---
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// --- Parse JSON body ---
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body']);
    exit;
}

$type    = trim($data['type']    ?? '');
$subject = trim($data['subject'] ?? '');
$body    = trim($data['body']    ?? '');

// Validate
if (!in_array($type, ['no_submission', 'submitted'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipient type']);
    exit;
}
if (empty($subject) || empty($body)) {
    echo json_encode(['success' => false, 'message' => 'Subject and body are required']);
    exit;
}
if (strlen($subject) > 255) {
    echo json_encode(['success' => false, 'message' => 'Subject too long']);
    exit;
}

// --- DB ---
require_once __DIR__ . '/config/database.php';
$database = new Database();
$db       = $database->getConnection();

// Fetch recipients
if ($type === 'no_submission') {
    // Registered but no row in submissions
    $stmt = $db->prepare("
        SELECT r.fullName AS name, r.email
        FROM registrations r
        LEFT JOIN submissions s ON s.userCode = r.userCode
        WHERE s.id IS NULL
          AND r.email IS NOT NULL
          AND r.email <> ''
    ");
} else {
    // Everyone who has submitted
    $stmt = $db->prepare("
        SELECT DISTINCT userName AS name, userEmail AS email
        FROM submissions
        WHERE userEmail IS NOT NULL AND userEmail <> ''
    ");
}
$stmt->execute();
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($recipients)) {
    echo json_encode(['success' => false, 'message' => 'No recipients found for this campaign']);
    exit;
}

// --- Send emails ---
$sentCount   = 0;
$failedCount = 0;
$fromName    = 'GREATER Art Competition';
$fromEmail   = 'info@greaterproject.eu';

foreach ($recipients as $recipient) {
    $personalName = htmlspecialchars($recipient['name'] ?? 'Participant');
    $recipEmail   = filter_var($recipient['email'], FILTER_VALIDATE_EMAIL);
    if (!$recipEmail) { $failedCount++; continue; }

    // Personalise body: replace {name} placeholder
    $personalBody = str_replace('{name}', $personalName, $body);

    $htmlBody = buildEmailHtml($personalName, $subject, $personalBody);

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Campaign-Type: {$type}\r\n";

    $mailSent = @mail($recipEmail, $subject, $htmlBody, $headers);
    if ($mailSent) {
        $sentCount++;
    } else {
        $failedCount++;
        error_log("[EmailCampaign] Failed to send to {$recipEmail}");
    }

    // Small delay to avoid overwhelming mail server
    usleep(50000); // 50ms
}

// --- Log campaign ---
try {
    $logStmt = $db->prepare("
        INSERT INTO email_campaigns (subject, body, recipient_type, sent_count, failed_count, sent_at, sent_by)
        VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    $logStmt->execute([$subject, $body, $type, $sentCount, $failedCount, $_SESSION['user_id']]);
} catch (Exception $e) {
    error_log("[EmailCampaign] Failed to log campaign: " . $e->getMessage());
}

echo json_encode([
    'success' => true,
    'sent'    => $sentCount,
    'failed'  => $failedCount,
    'total'   => count($recipients)
]);
exit;


// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function buildEmailHtml(string $name, string $subject, string $body): string {
    $bodyHtml = nl2br(htmlspecialchars($body));
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$subject}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#667eea,#764ba2);padding:32px 40px;text-align:center;">
              <div style="color:#fff;font-size:26px;font-weight:700;letter-spacing:1px;">GREATER</div>
              <div style="color:rgba(255,255,255,0.85);font-size:14px;margin-top:4px;">Art Competition 2025</div>
            </td>
          </tr>
          <!-- Body -->
          <tr>
            <td style="padding:36px 40px;">
              <p style="font-size:16px;color:#374151;margin:0 0 18px;">Dear <strong>{$name}</strong>,</p>
              <div style="font-size:15px;color:#4b5563;line-height:1.7;margin:0 0 24px;">
                {$bodyHtml}
              </div>
              <hr style="border:none;border-top:1px solid #e5e7eb;margin:28px 0;">
              <p style="font-size:13px;color:#9ca3af;margin:0;">
                If you have any questions, contact us at
                <a href="mailto:info@greaterproject.eu" style="color:#667eea;">info@greaterproject.eu</a>
              </p>
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td style="background:#f9fafb;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
              <p style="font-size:12px;color:#9ca3af;margin:0;">
                © 2025 GREATER Art Competition · Co-funded by Erasmus+
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}
