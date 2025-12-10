<?php if ($error): ?>
    <div style="color: red; padding: 10px; border: 1px solid red;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green; padding: 10px; border: 1px solid green;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($user): ?>
    <h1>Modifier l'utilisateur: <?php echo htmlspecialchars($user['name']); ?></h1>

    <form method="POST">
        <div>
            <label for="name">Nom:</label>
            <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>

        <div>
            <label for="role">Rôle:</label>
            <select name="role" id="role" required>
                <option value="1" <?php echo $user['id_role'] == 1 ? 'selected' : ''; ?>>Admin</option>
                <option value="2" <?php echo $user['id_role'] == 2 ? 'selected' : ''; ?>>Utilisateur</option>
            </select>
        </div>

        <div>
            <label for="is_active">
                <input type="checkbox" name="is_active" id="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                Utilisateur actif
            </label>
        </div>

        <button type="submit">Mettre à jour</button>
    </form>

<?php else: ?>
    <p>Utilisateur non trouvé.</p>
<?php endif; ?>

<br>
<a href="/users-management">Retour à la liste</a>
