<?php
require_once 'configuration.php';
require_once CHEMIN_ACCESSEUR . 'AccesseurConnexion.php';
require_once 'Email.php';
use App\Accesseur\AccesseurConnexion;

$email = $_POST['email'] ?? '';
$acc = new AccesseurConnexion();
$token = $acc->createResetToken($email);
if (!$token) {
    echo 'Aucun compte trouvé pour cet e-mail.';
    exit;
}

$resetLink = BASE_URL . '/reset.php?token=' . $token;
$objet = 'Réinitialisation du mot de passe';
$contenu = "Cliquez <a href=\"$resetLink\">ici</a> pour réinitialiser votre mot de passe (valide 1h).";
$mail = new Email();
$mail->email($email, $objet, $contenu);

echo "Un email a été envoyé si l'adresse existe dans notre système.";
