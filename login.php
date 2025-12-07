<?php session_start(); ?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Connexion</title>
</head>

<body>
    <h2>Connexion</h2>
    <?php if (!empty($_GET['error'])): ?>
        <p style="color:red"><?php echo htmlspecialchars($_GET['error']); ?></p><?php endif; ?>
    <form action="actionLogin.php" method="post">
        <label>Email:<br><input type="email" name="email" required></label><br>
        <label>Mot de passe:<br><input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>
    <p><a href="forgot.php">Mot de passe oublié</a></p>
    <p>Pas encore inscrit ? <a href="register.php">S'inscrire</a></p>
</body>

</html>