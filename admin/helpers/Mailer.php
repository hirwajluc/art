<?php
/**
 * SMTP mailer — no external dependencies.
 * Hardcoded for greaterproject.eu on Aruba hosting (smtp.aruba.it:587 STARTTLS).
 */
class Mailer {

    private $host      = 'smtp.aruba.it';
    private $port      = 25;
    private $user      = 'no_reply@greaterproject.eu';
    private $pass      = 'Hirwa@123';
    private $fromEmail = 'no_reply@greaterproject.eu';
    private $fromName  = 'GREATER Art Competition';

    /** Send an HTML email. Returns true on success, error string on failure. */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
        try {
            return $this->sendSMTP($toEmail, $toName, $subject, $htmlBody);
        } catch (Exception $e) {
            error_log("[Mailer] SMTP error: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    private function sendSMTP(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
        $sock = @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, 15);
        if (!$sock) {
            return "Cannot connect to {$this->host}:{$this->port} — {$errstr}";
        }
        stream_set_timeout($sock, 15);

        $this->expect($sock, 220);
        $this->cmd($sock, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'greaterproject.eu'));
        $this->readAll($sock);

        // STARTTLS
        $this->cmd($sock, "STARTTLS");
        $this->expect($sock, 220);
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return "STARTTLS negotiation failed";
        }
        $this->cmd($sock, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'greaterproject.eu'));
        $this->readAll($sock);

        // AUTH LOGIN
        $this->cmd($sock, "AUTH LOGIN");
        $this->expect($sock, 334);
        $this->cmd($sock, base64_encode($this->user));
        $this->expect($sock, 334);
        $this->cmd($sock, base64_encode($this->pass));
        $this->expect($sock, 235, 'SMTP authentication failed — check credentials');

        // Envelope
        $this->cmd($sock, "MAIL FROM:<{$this->fromEmail}>");
        $this->expect($sock, 250);
        $this->cmd($sock, "RCPT TO:<{$toEmail}>");
        $this->expect($sock, 250, "Recipient rejected: {$toEmail}");

        // DATA
        $this->cmd($sock, "DATA");
        $this->expect($sock, 354);

        $boundary    = md5(uniqid());
        $safeSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $safeFrom    = '=?UTF-8?B?' . base64_encode($this->fromName) . '?=';
        $safeTo      = $toName ? ('=?UTF-8?B?' . base64_encode($toName) . '?= <' . $toEmail . '>') : $toEmail;
        $plainText   = strip_tags(str_replace(['<br>', '<br/>', '<br />', '<p>', '</p>'], "\n", $htmlBody));

        $msg  = "Date: " . date('r') . "\r\n";
        $msg .= "From: {$safeFrom} <{$this->fromEmail}>\r\n";
        $msg .= "To: {$safeTo}\r\n";
        $msg .= "Subject: {$safeSubject}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $msg .= "\r\n";
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n{$plainText}\r\n";
        $msg .= "--{$boundary}\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n{$htmlBody}\r\n";
        $msg .= "--{$boundary}--\r\n";
        $msg .= "\r\n.";

        fwrite($sock, $msg . "\r\n");
        $this->expect($sock, 250, 'Message rejected by server');

        $this->cmd($sock, "QUIT");
        fclose($sock);
        return true;
    }

    private function cmd($sock, string $c): void {
        fwrite($sock, $c . "\r\n");
    }

    private function readAll($sock): string {
        $out = '';
        while ($line = fgets($sock, 512)) {
            $out .= $line;
            if ($line[3] === ' ') break;
        }
        return $out;
    }

    private function expect($sock, int $code, string $errMsg = ''): void {
        $resp = $this->readAll($sock);
        if ((int)substr($resp, 0, 3) !== $code) {
            throw new RuntimeException($errMsg ?: "Expected {$code}, got: " . trim($resp));
        }
    }
}
