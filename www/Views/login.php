<div class="auth-container">
    <h2>Connexion</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>

    <p class="auth-links">
        <a href="/register">S'inscrire</a> |
        <a href="/password-reset">Mot de passe oublié ?</a>
    </p>
</div>
<br>
<a href="/">Accueil</a>
