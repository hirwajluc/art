<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$to = "hirwajluc@gmail.com";
$subject = "Aruba PHP mail() test";
$message = "Hello, this is a test message from Aruba PHP mail().";
$headers  = "From: info@greaterproject.eu\r\n";
$headers .= "Reply-To: info@greaterproject.eu\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed.";
}
?>