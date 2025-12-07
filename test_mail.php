<?php
// Simple test script to send an email using the project's Email class.
// Usage: open in browser: http://yourhost/phptest/test_mail.php?to=your.email@example.com

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/configuration.php';
require __DIR__ . '/Email.php';

$to = $_GET['to'] ?? (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'phptest@mailo.com');
$subject = 'Test email from phptest';
$body = '<p>This is a test email sent at ' . date('Y-m-d H:i:s') . ' from the phptest app.</p>';

echo 'Sending test email to: ' . htmlspecialchars($to) . "<br>\n";

$mailer = new Email();
$mailer->email($to, $subject, $body);

echo "<br>Done. If there were errors they should be shown above.\n";
echo "<br>Check the recipient inbox and your SMTP provider's sent/logs.\n";

?>