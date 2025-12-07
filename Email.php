<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Try to load Composer autoload if available. If it's missing we fall back to PHP's mail().
$autoload = __DIR__ . '/vendor/autoload.php';
$composer_present = false;
if (file_exists($autoload)) {
    require $autoload;
    $composer_present = true;
}

class Email
{
    function email($destinataire, $objet, $contenu)
    {
        global $composer_present;

        // If PHPMailer is available, use it.
        if ($composer_present && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new PHPMailer(true);

            try {
                /* DONNEES SERVEUR */
                #####################
                $mail->CharSet = 'utf-8';
                $mail->SMTPDebug = 0;            // en production (sinon "2")
                $mail->isSMTP();                                                            // envoi avec le SMTP du serveur
                // Use configuration constants when available, otherwise fall back to sensible defaults
                $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'mail.mailo.com';
                $mail->SMTPAuth = true;                                            // le serveur SMTP nécessite une authentification ("false" sinon)
                $mail->Username = defined('SMTP_USER') ? SMTP_USER : 'phptest@mailo.com';     // login SMTP
                $mail->Password = defined('SMTP_PASS') ? SMTP_PASS : '';                                                // Mot de passe SMTP
                $secure = defined('SMTP_SECURE') ? SMTP_SECURE : 'ssl';
                if ($secure === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($secure === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 465;                                                               // port TCP (ou 25, ou 465...)

                /* DONNEES DESTINATAIRES */
                ##########################
                $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : (defined('SMTP_USER') ? SMTP_USER : 'phptest@mailo.com');
                $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'PHP Test';
                $mail->setFrom($fromEmail, $fromName);  //adresse de l'expéditeur (pas d'accents)
                $mail->addAddress($destinataire);        // Adresse du destinataire (le nom est facultatif)
                /* CONTENU DE L'EMAIL*/
                ##########################
                $mail->isHTML(true);                                      // email au format HTML
                $mail->Subject = $objet;      // Objet du message (éviter les accents là, sauf si utf8_encode)
                $mail->Body = $contenu;

                $mail->send();
            }
            // si le try ne marche pas > exception ici
            catch (Exception $e) {
                echo "Le Message n'a pas été envoyé. Mailer Error: {$mail->ErrorInfo}"; // Affiche l'erreur concernée le cas échéant
            }

            return;
        }

        // If PHPMailer is not installed, try PHP's mail() as a fallback.
        $headers = "MIME-Version: 1.0\\r\\n";
        $headers .= "Content-type:text/html;charset=UTF-8\\r\\n";
        $headers .= "From: MatRevente <matrevente@mailo.com>\\r\\n";

        if (!@mail($destinataire, $objet, $contenu, $headers)) {
            echo "Le Message n'a pas été envoyé. Aucune librairie d'envoi (PHPMailer) trouvée et la fonction mail() a échoué.";
        }
    }
}
?>