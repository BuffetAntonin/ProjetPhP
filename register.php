<?php
// Simple registration form
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Inscription</title>
</head>

<body>
    <h2>Inscription</h2>
    <form action="actionInscription.php" method="post">
        <label>Nom complet:<br><input type="text" name="name" required></label><br>
        <label>Email:<br><input type="email" name="email" required></label><br>
        <label>Mot de passe:<br><input type="password" name="password" required></label><br>
        <button type="submit">S'inscrire</button>
    </form>
    <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
</body>

</html>