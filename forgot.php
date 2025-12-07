<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Mot de passe oublié</title>
</head>

<body>
    <h2>Mot de passe oublié</h2>
    <form action="actionForgot.php" method="post">
        <label>Email:<br><input type="email" name="email" required></label><br>
        <button type="submit">Envoyer le lien de réinitialisation</button>
    </form>
    <p><a href="login.php">Retour connexion</a></p>
</body>

</html>