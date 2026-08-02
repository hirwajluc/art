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

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $log[] = '❌ Invalid recipient email address.';
    } else {
        $body = "<p>This is a test email sent from <strong>" . htmlspecialchars($_SERVER['HTTP_HOST']) . "</strong> at " . date('Y-m-d H:i:s') . ".</p>"
              . "<p>From: <strong>" . htmlspecialchars($from) . "</strong></p>"
              . "<p>Method: <strong>" . htmlspecialchars($method) . "</strong></p>";

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

        if ($method === 'mail_plain') {
            // Plain mail() no envelope sender
            $ok = @mail($to, $subject, $body, $headers);
            $log[] = $ok ? '✅ mail() returned TRUE (no -f param)' : '❌ mail() returned FALSE (no -f param)';
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
      <option value="mail_plain"   <?php echo ($_POST['method']??'')==='mail_plain'   ?'selected':''; ?>>mail() — plain (no envelope sender)</option>
      <option value="mail_with_f" <?php echo ($_POST['method']??'')==='mail_with_f' ?'selected':''; ?>>mail() — with -f envelope sender</option>
      <option value="smtp"        <?php echo ($_POST['method']??'')==='smtp'        ?'selected':''; ?>>SMTP probe — smtp.aruba.it:25 (no send, just connect+auth test)</option>
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

<script>
document.querySelector('[name=method]').addEventListener('change', function(){
  document.getElementById('smtp_fields').style.display = this.value === 'smtp' ? 'block' : 'none';
});
</script>
</body>
</html>
