<?php
/**
 * Simple SMTP mailer — no external dependencies.
 * Reads config from competition_settings table (smtp_* keys).
 * Falls back to PHP mail() only if no SMTP host is configured.
 */
class Mailer {

    private $host;
    private $port;
    private $user;
    private $pass;
    private $encryption; // 'tls' | 'ssl' | ''
    private $fromEmail;
    private $fromName;

    public function __construct(array $cfg = []) {
        $this->host       = $cfg['smtp_host']      ?? '';
        $this->port       = (int)($cfg['smtp_port'] ?? 587);
        $this->user       = $cfg['smtp_username']  ?? '';
        $this->pass       = $cfg['smtp_password']  ?? '';
        $this->encryption = strtolower($cfg['smtp_encryption'] ?? 'tls');
        $this->fromEmail  = $cfg['smtp_from_email'] ?? 'info@greaterproject.eu';
        $this->fromName   = $cfg['smtp_from_name']  ?? 'GREATER Art Competition';
    }

    /** Load settings from competition_settings table and return a Mailer instance. */
    public static function fromDB(PDO $db): self {
        try {
            $rows = $db->query("SELECT setting_key, setting_value FROM competition_settings WHERE setting_key LIKE 'smtp_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $rows = [];
        }
        return new self($rows);
    }

    /** Send an HTML email. Returns true on success, error string on failure. */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
        if (empty($this->host)) {
            // No SMTP configured — fall back to mail()
            $headers  = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $ok = @mail($toEmail, $subject, $htmlBody, $headers);
            return $ok ? true : 'mail() failed — configure SMTP in Settings';
        }

        try {
            return $this->sendSMTP($toEmail, $toName, $subject, $htmlBody);
        } catch (Exception $e) {
            error_log("[Mailer] SMTP error: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    private function sendSMTP(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
        $port = $this->port;
        $host = $this->host;

        // Open socket
        if ($this->encryption === 'ssl') {
            $socket = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 15);
        } else {
            $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 15);
        }

        if (!$socket) {
            return "Cannot connect to {$host}:{$port} — {$errstr}";
        }

        stream_set_timeout($socket, 15);

        $this->expect($socket, 220);

        $this->cmd($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $ehlo = $this->readAll($socket);

        // STARTTLS upgrade
        if ($this->encryption === 'tls') {
            $this->cmd($socket, "STARTTLS");
            $this->expect($socket, 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                return "STARTTLS negotiation failed";
            }
            $this->cmd($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $this->readAll($socket);
        }

        // AUTH LOGIN
        $this->cmd($socket, "AUTH LOGIN");
        $this->expect($socket, 334);
        $this->cmd($socket, base64_encode($this->user));
        $this->expect($socket, 334);
        $this->cmd($socket, base64_encode($this->pass));
        $this->expect($socket, 235, 'Authentication failed — check username/password');

        // Envelope
        $this->cmd($socket, "MAIL FROM:<{$this->fromEmail}>");
        $this->expect($socket, 250);
        $this->cmd($socket, "RCPT TO:<{$toEmail}>");
        $this->expect($socket, 250, "Recipient rejected: {$toEmail}");

        // Message body
        $this->cmd($socket, "DATA");
        $this->expect($socket, 354);

        $date    = date('r');
        $boundary = md5(uniqid());
        $safeSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $safeFrom    = '=?UTF-8?B?' . base64_encode($this->fromName) . '?=';
        $safeTo      = $toName ? ('=?UTF-8?B?' . base64_encode($toName) . '?= <' . $toEmail . '>') : $toEmail;

        $msg  = "Date: {$date}\r\n";
        $msg .= "From: {$safeFrom} <{$this->fromEmail}>\r\n";
        $msg .= "To: {$safeTo}\r\n";
        $msg .= "Subject: {$safeSubject}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $msg .= "\r\n";
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $msg .= strip_tags(str_replace(['<br>', '<br/>', '<br />','<p>','</p>'], "\n", $htmlBody)) . "\r\n";
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $msg .= $htmlBody . "\r\n";
        $msg .= "--{$boundary}--\r\n";
        $msg .= "\r\n.";

        fwrite($socket, $msg . "\r\n");
        $this->expect($socket, 250, 'Message rejected by server');

        $this->cmd($socket, "QUIT");
        fclose($socket);
        return true;
    }

    private function cmd($socket, string $cmd): void {
        fwrite($socket, $cmd . "\r\n");
    }

    private function readAll($socket): string {
        $out = '';
        while ($line = fgets($socket, 512)) {
            $out .= $line;
            if ($line[3] === ' ') break; // last line of multi-line response
        }
        return $out;
    }

    private function expect($socket, int $code, string $errMsg = ''): void {
        $resp = $this->readAll($socket);
        if ((int)substr($resp, 0, 3) !== $code) {
            throw new RuntimeException($errMsg ?: "Expected {$code}, got: " . trim($resp));
        }
    }
}
