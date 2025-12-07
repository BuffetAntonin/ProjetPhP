Setup notes (simple)

1) Database
   - Create a database (e.g., `mini_wordpress`) and run the SQL in `schema.sql`.

2) Composer / PHPMailer
   - This project uses PHPMailer via Composer. From the project folder run:
       composer require phpmailer/phpmailer

3) Edit `configuration.php`
   - Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` and `BASE_URL`.

4) Test registration
   - Open `register.php`, create a new user.
   - Check the activation email (server must allow SMTP settings in `Email.php`).
