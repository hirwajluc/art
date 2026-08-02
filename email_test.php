<?php
/**
 * Standalone email test page — no login required.
 * DELETE THIS FILE after confirming email works.
 */

$log    = [];
$sent   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to     = trim($_POST['to']   ?? '');
    $from   = trim($_POST['from'] ?? 'no_reply@greaterproject.eu');
    $method = $_POST['method']    ?? 'mail_plain';

    $log[] = "📌 PHP: " . phpversion();
    $log[] = "📌 Server: " . ($_SERVER['HTTP_HOST'] ?? 'unknown');
    $log[] = "📌 sendmail_path: " . ini_get('sendmail_path');
    $log[] = "📌 To: {$to}";
    $log[] = "📌 From: {$from}";
    $log[] = "📌 Method: {$method}";

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $log[] = '❌ Invalid recipient email address.';
    } else {

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: GREATER Art Competition <{$from}>\r\n";
        $headers .= "Reply-To: info@greaterproject.eu\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        if ($method === 'mail_plain') {
            $subject = 'GREATER Email Test — plain';
            $body    = "<p>Test email at " . date('Y-m-d H:i:s') . ".</p>";
            $ok      = mail($to, $subject, $body, $headers);
            $log[]   = $ok ? '✅ mail() = TRUE' : '❌ mail() = FALSE';

        } elseif ($method === 'mail_rich') {
            $subject = 'GREATER Email Test — rich HTML';
            $body    = '<!DOCTYPE html><html><body style="font-family:Arial;background:#f3f4f6;padding:40px 0;">'
                     . '<table width="600" style="background:#fff;border-radius:16px;margin:0 auto;padding:40px;">'
                     . '<tr><td style="background:linear-gradient(135deg,#667eea,#764ba2);padding:32px;text-align:center;border-radius:12px 12px 0 0;">'
                     . '<div style="color:#fff;font-size:26px;font-weight:700;">GREATER</div></td></tr>'
                     . '<tr><td style="padding:36px;">'
                     . '<p>Rich HTML test at ' . date('Y-m-d H:i:s') . '</p>'
                     . '<p><a href="https://www.greaterproject.eu/art/admin/?page=set_password&token=TEST123" style="background:#1E90FF;color:#fff;padding:14px 36px;border-radius:8px;text-decoration:none;">Set My Password</a></p>'
                     . '</td></tr></table></body></html>';
            $ok    = mail($to, $subject, $body, $headers);
            $log[] = $ok ? '✅ mail() = TRUE' : '❌ mail() = FALSE';

        } elseif ($method === 'mail_judge_exact') {
            // Exact replica of createJudge() email — same subject, same HTML builder
            require_once __DIR__ . '/admin/models/Judging.php';
            $j       = new Judging();
            $link    = 'https://' . $_SERVER['HTTP_HOST'] . '/art/admin/?page=set_password&token=TESTTOKEN123';
            $subject = 'You have been invited as a judge — GREATER Art Competition';
            $body    = $j->buildJudgeInviteHtmlPublic('Test Judge', 'testjudge', $link);
            $ok      = mail($to, $subject, $body, $headers);
            $log[]   = $ok ? '✅ mail() = TRUE (judge exact)' : '❌ mail() = FALSE (judge exact)';
            $log[]   = "Subject used: {$subject}";
        }

        $log[] = '';
        $log[] = "📌 Result logged above.";
    }
}

// Show mail_debug.log written by createJudge()
$debugLog = '';
foreach ([__DIR__ . '/mail_debug.log', __DIR__ . '/../mail_debug.log'] as $dl) {
    if (file_exists($dl)) {
        $debugLog = file_get_contents($dl);
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Email Test — GREATER</title>
<style>
  body { font-family: Arial, sans-serif; max-width: 820px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
  h1 { color: #1E90FF; }
  .card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.1); margin-bottom: 24px; }
  label { display: block; font-weight: 600; margin-bottom: 4px; color: #333; }
  input, select { width: 100%; padding: 10px; border: 1.5px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 14px; box-sizing: border-box; }
  button { background: #1E90FF; color: #fff; border: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; cursor: pointer; }
  .log { background: #1a1a2e; color: #e0e0e0; border-radius: 8px; padding: 20px; font-family: monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; line-height: 1.7; }
  .warn { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
</style>
</head>
<body>
<h1>📧 Email Test</h1>
<div class="warn">⚠️ Delete <code>email_test.php</code> after testing.</div>

<div class="card">
  <form method="POST">
    <label>Send To</label>
    <input type="email" name="to" value="<?php echo htmlspecialchars($_POST['to'] ?? 'hirwajluc@gmail.com'); ?>" required>

    <label>From</label>
    <input type="text" name="from" value="<?php echo htmlspecialchars($_POST['from'] ?? 'no_reply@greaterproject.eu'); ?>">

    <label>Method</label>
    <select name="method">
      <?php foreach ([
          'mail_plain'       => 'mail() — simple body (confirmed working)',
          'mail_rich'        => 'mail() — rich HTML body',
          'mail_judge_exact' => 'mail() — EXACT judge invite (same code as createJudge)',
      ] as $val => $label): ?>
      <option value="<?php echo $val; ?>" <?php echo (($_POST['method'] ?? '') === $val) ? 'selected' : ''; ?>>
        <?php echo $label; ?>
      </option>
      <?php endforeach; ?>
    </select>

    <button type="submit">Send Test</button>
  </form>
</div>

<?php if (!empty($log)): ?>
<div class="card">
  <h3 style="margin-top:0;">Result</h3>
  <div class="log"><?php echo htmlspecialchars(implode("\n", $log)); ?></div>
</div>
<?php endif; ?>

<?php if ($debugLog): ?>
<div class="card">
  <h3 style="margin-top:0;">📋 mail_debug.log (from judge creation)</h3>
  <div class="log"><?php echo htmlspecialchars($debugLog); ?></div>
</div>
<?php endif; ?>

</body>
</html>
