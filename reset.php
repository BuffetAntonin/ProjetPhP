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
$row = $acc->verifyResetToken($token);
if (!$row) {
    echo 'Token invalide ou expiré. Veuillez recommencer la procédure de réinitialisation.';
    exit;
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Réinitialiser le mot de passe</title>
</head>

<body>
    <h2>Réinitialiser le mot de passe</h2>
    <form action="actionReset.php" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label>Nouveau mot de passe:<br><input type="password" name="password" required></label><br>
        <button type="submit">Réinitialiser</button>
    </form>
</body>

</html>