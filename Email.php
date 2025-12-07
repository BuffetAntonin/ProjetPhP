<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class Email
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = getenv('SMTP_USERNAME') ?: 'your-email@gmail.com';
            $this->mail->Password = getenv('SMTP_PASSWORD') ?: 'your-app-password';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;

            // Set sender
            $this->mail->setFrom(getenv('SMTP_FROM') ?: 'noreply@phptest.local', 'PHP Test App');
        } catch (Exception $e) {
            error_log("Mailer Error: {$this->mail->ErrorInfo}");
        }
    }

    public function email($recipientEmail, $subject, $body)
    {
        try {
            $this->mail->addAddress($recipientEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email send failed: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
