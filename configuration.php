<?php
// Simple configuration - edit DB settings for your environment
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'phpexam');
define('DB_USER', 'root');
define('DB_PASS', '');

// Path to accessor files (empty = same directory)
define('CHEMIN_ACCESSEUR', '');

// Base URL for links used in emails (edit to match your local host)
// Update this to include the project folder so emailed links point to the right path.
// If you access the app at "http://localhost/web/phptest" set BASE_URL accordingly.
define('BASE_URL', 'http://localhost/web/phptest');

// SMTP / Email settings (update for your mail provider)
define('SMTP_HOST', 'mail.mailo.com');
define('SMTP_USER', 'phptest@mailo.com');
define('SMTP_PASS', 'Phptest1234');
define('SMTP_SECURE', 'ssl'); // 'ssl' or 'tls' or ''
define('SMTP_PORT', 465);
define('SMTP_FROM_EMAIL', 'phptest@mailo.com');
define('SMTP_FROM_NAME', 'PHP Test');

// Reset token lifetime in seconds (default 2 hours). Increase if you want longer validity.
define('RESET_TOKEN_LIFETIME', 7200);

// PDO helper
function getPDO()
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $opt);
}

?>