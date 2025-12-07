<?php
require_once 'configuration.php';
require_once CHEMIN_ACCESSEUR . 'AccesseurConnexion.php';
use App\Accesseur\AccesseurConnexion;

$token = $_GET['token'] ?? '';
if (!$token) {
    echo 'Token manquant.';
    exit;
}

$acc = new AccesseurConnexion();
if ($acc->activate($token)) {
    echo 'Compte activé. <a href="login.php">Se connecter</a>';
} else {
    echo 'Token invalide ou compte déjà activé.';
}
