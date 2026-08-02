<?php
class Mailer {

    private $fromEmail = 'no_reply@greaterproject.eu';
    private $fromName  = 'GREATER Art Competition';

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: info@greaterproject.eu\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $ok = @mail($toEmail, $subject, $htmlBody, $headers);
        return $ok ? true : 'mail() returned false — check server mail configuration';
    }
}
