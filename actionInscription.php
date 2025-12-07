<?php
use App\Modele\Utilisateur;
use App\Accesseur\AccesseurConnexion;

require "configuration.php";
require CHEMIN_ACCESSEUR . "AccesseurConnexion.php";
require_once "Email.php";
require_once "Utilisateur.php";

$unUtilisateur = new Utilisateur(
    $_POST
);

$unUtilisateurAccesseur = new AccesseurConnexion();
$msg = $unUtilisateurAccesseur->inscription($unUtilisateur);
if ($msg == "") {
    $token = $unUtilisateur->getActivation_token();
    $destinataire = $unUtilisateur->getEmail();
    $objet = "Activation du compte";
    // Build activation link using BASE_URL from configuration.php so links match your host
    $activationLink = (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/activeCompte.php?token=' . $token;
    $contenu = "Cliquez <a href=\"$activationLink\">ici</a> pour activer votre compte.";
    $email = new Email();
    $email->email($destinataire, $objet, $contenu);
    header("Status: 302 Found", false, 302);
    header("Location: ./login.php");
    exit;
}

?>