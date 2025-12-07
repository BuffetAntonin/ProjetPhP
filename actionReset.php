<?php
require_once 'configuration.php';
require_once CHEMIN_ACCESSEUR . 'AccesseurConnexion.php';
use App\Accesseur\AccesseurConnexion;

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
if (!$token || !$password) {
    echo 'Données manquantes.';
    exit;
}

$acc = new AccesseurConnexion();
$ok = $acc->resetPassword($token, $password);
if ($ok) {
    echo 'Mot de passe mis à jour. <a href="login.php">Se connecter</a>';
} else {
    echo 'Erreur lors de la réinitialisation du mot de passe.';
}
