<?php
/**
 * Standalone email test page — no login required.
 * Access at: /art/email_test.php
 * DELETE THIS FILE after confirming email works.
 */

$log    = [];
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to      = trim($_POST['to']      ?? '');
    $from    = trim($_POST['from']    ?? 'no_reply@greaterproject.eu');
    $subject = trim($_POST['subject'] ?? 'GREATER Email Test');
    $method  = $_POST['method'] ?? 'mail';

    // Read debug log if it exists
    $debugLog = '';
    foreach ([__DIR__ . '/mail_debug.log', __DIR__ . '/../mail_debug.log'] as $dl) {
        if (file_exists($dl)) { $debugLog = file_get_contents($dl); break; }
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $log[] = '❌ Invalid recipient email address.';
    } else {
        $simpleBody = "<p>This is a test email sent from <strong>" . htmlspecialchars($_SERVER['HTTP_HOST']) . "</strong> at " . date('Y-m-d H:i:s') . ".</p>"
                    . "<p>From: <strong>" . htmlspecialchars($from) . "</strong></p>"
                    . "<p>Method: <strong>" . htmlspecialchars($method) . "</strong></p>";

        // Judge invite style body
        $richBody = '<!DOCTYPE html><html><body style="font-family:Arial;background:#f3f4f6;padding:40px 0;">'
                  . '<table width="600" style="background:#fff;border-radius:16px;margin:0 auto;padding:40px;">'
                  . '<tr><td style="background:linear-gradient(135deg,#667eea,#764ba2);padding:32px;text-align:center;border-radius:12px 12px 0 0;">'
                  . '<div style="color:#fff;font-size:26px;font-weight:700;">GREATER</div>'
                  . '<div style="color:rgba(255,255,255,.85);font-size:14px;">Art Competition 2025</div></td></tr>'
                  . '<tr><td style="padding:36px;">'
                  . '<p>Dear <strong>Test Judge</strong>,</p>'
                  . '<p>You have been invited to serve as a judge for the GREATER Art Competition.</p>'
                  . '<p><strong>Username:</strong> testjudge</p>'
                  . '<p style="text-align:center;margin:30px 0;">'
                  . '<a href="https://www.greaterproject.eu/art/admin/?page=set_password&token=TEST123" style="background:#1E90FF;color:#fff;padding:14px 36px;border-radius:8px;text-decoration:none;font-weight:bold;">Set My Password</a>'
                  . '</p></td></tr></table></body></html>';

        $body = ($method === 'mail_rich') ? $richBody : $simpleBody;

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: GREATER Art Competition <{$from}>\r\n";
        $headers .= "Reply-To: info@greaterproject.eu\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $log[] = "📤 Sending to: {$to}";
        $log[] = "📤 From: {$from}";
        $log[] = "📤 Subject: {$subject}";
        $log[] = "📤 Server: " . php_uname('n');
        $log[] = "📤 PHP: " . phpversion();
        $log[] = "📤 Sendmail path: " . ini_get('sendmail_path');

        if ($method === 'mail_plain' || $method === 'mail_rich') {
            // Plain mail() no envelope sender
            $ok = mail($to, $subject, $body, $headers);
            $log[] = $ok ? '✅ mail() returned TRUE' : '❌ mail() returned FALSE';
        } elseif ($method === 'mail_with_f') {
            // mail() with envelope sender
            $ok = @mail($to, $subject, $body, $headers, "-f {$from}");
            $log[] = $ok ? "✅ mail() returned TRUE (with -f {$from})" : "❌ mail() returned FALSE (with -f {$from})";
        } elseif ($method === 'smtp') {
            // Raw SMTP to smtp.aruba.it
            $host = 'smtp.aruba.it';
            $port = 25;
            $log[] = "📡 Trying TCP connect to {$host}:{$port}...";
            $sock = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 10);
            if (!$sock) {
                $log[] = "❌ Cannot connect: {$errstr} (errno {$errno})";
            } else {
                stream_set_timeout($sock, 10);
                $read = function($s) { $o=''; while($l=fgets($s,512)){$o.=$l;if($l[3]===' ')break;} return trim($o); };
                $log[] = '< ' . $read($sock);
                fwrite($sock, "EHLO {$_SERVER['HTTP_HOST']}\r\n"); $log[] = '< ' . $read($sock);
                fwrite($sock, "STARTTLS\r\n"); $r = $read($sock); $log[] = '< ' . $r;
                if (strpos($r, '220') !== false) {
                    stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                    fwrite($sock, "EHLO {$_SERVER['HTTP_HOST']}\r\n"); $log[] = '< ' . $read($sock);
                }
                fwrite($sock, "AUTH LOGIN\r\n"); $log[] = '< ' . $read($sock);
                fwrite($sock, base64_encode($from) . "\r\n"); $log[] = '< ' . $read($sock);
                fwrite($sock, base64_encode($_POST['smtp_pass'] ?? '') . "\r\n"); $log[] = '< ' . $read($sock);
                fwrite($sock, "QUIT\r\n");
                fclose($sock);
            }
            $ok = false;
        }

        $log[] = "📌 sendmail_path: " . ini_get('sendmail_path');
        $log[] = "📌 SMTP (php.ini): " . ini_get('SMTP');
        $log[] = "📌 smtp_port: " . ini_get('smtp_port');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Email Test — GREATER</title>
<style>
  body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
  h1 { color: #1E90FF; }
  .card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.1); margin-bottom: 24px; }
  label { display: block; font-weight: 600; margin-bottom: 4px; color: #333; }
  input, select { width: 100%; padding: 10px; border: 1.5px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 14px; box-sizing: border-box; }
  button { background: #1E90FF; color: #fff; border: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; cursor: pointer; }
  button:hover { background: #187bcd; }
  .log { background: #1a1a2e; color: #e0e0e0; border-radius: 8px; padding: 20px; font-family: monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; line-height: 1.7; }
  .warn { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
</style>
</head>
<body>
<h1>📧 Email Test Page</h1>
<div class="warn">⚠️ Delete this file (<code>email_test.php</code>) after testing.</div>

<div class="card">
  <form method="POST">
    <label>Send To</label>
    <input type="email" name="to" value="<?php echo htmlspecialchars($_POST['to'] ?? 'hirwajluc@gmail.com'); ?>" required>

    <label>From Address</label>
    <input type="text" name="from" value="<?php echo htmlspecialchars($_POST['from'] ?? 'no_reply@greaterproject.eu'); ?>">

    <label>Subject</label>
    <input type="text" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? 'GREATER Email Test'); ?>">

    <label>Method</label>
    <select name="method">
      <option value="mail_plain"   <?php echo ($_POST['method']??'')==='mail_plain'   ?'selected':''; ?>>mail() — simple body (confirmed working)</option>
      <option value="mail_rich"    <?php echo ($_POST['method']??'')==='mail_rich'    ?'selected':''; ?>>mail() — rich HTML body (same as judge invite)</option>
      <option value="mail_with_f"  <?php echo ($_POST['method']??'')==='mail_with_f'  ?'selected':''; ?>>mail() — with -f envelope sender</option>
      <option value="smtp"         <?php echo ($_POST['method']??'')==='smtp'         ?'selected':''; ?>>SMTP probe — connect+auth test only</option>
    </select>

    <div id="smtp_fields" style="display:none;">
      <label>SMTP Password (for auth test only)</label>
      <input type="password" name="smtp_pass" value="">
    </div>

    <button type="submit">Send Test Email</button>
  </form>
</div>

<?php if (!empty($log)): ?>
<div class="card">
  <h3 style="margin-top:0;">Results</h3>
  <div class="log"><?php echo htmlspecialchars(implode("\n", $log)); ?></div>
</div>
<?php endif; ?>

<?php
$debugLog = '';
foreach ([__DIR__ . '/mail_debug.log', __DIR__ . '/../mail_debug.log'] as $dl) {
    if (file_exists($dl)) { $debugLog = file_get_contents($dl); break; }
}
if ($debugLog): ?>
<div class="card">
  <h3 style="margin-top:0;">📋 mail_debug.log (judge invite log)</h3>
  <div class="log"><?php echo htmlspecialchars($debugLog); ?></div>
</div>
<?php endif; ?>

<script>
document.querySelector('[name=method]').addEventListener('change', function(){
  document.getElementById('smtp_fields').style.display = this.value === 'smtp' ? 'block' : 'none';
});
</script>
</body>
</html>
