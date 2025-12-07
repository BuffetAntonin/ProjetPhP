<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Tableau de bord</title>
</head>

<body>
    <h2>Bienvenue <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
    <p>Vous êtes connecté.</p>
    <p><a href="logout.php">Se déconnecter</a></p>
</body>

</html>