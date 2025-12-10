<h1>Gestion des utilisateurs</h1>

<?php if (isset($error)): ?>
    <div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 20px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div style="color: green; padding: 10px; border: 1px solid green; margin-bottom: 20px;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<table border="1" cellpadding="10">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Actif</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td>
                <?php 
                    $roleId = $user['id_role'];
                    echo $roleId == 1 ? 'Admin' : 'Utilisateur';
                ?>
            </td>
            <td><?php echo $user['is_active'] ? 'Oui' : 'Non'; ?></td>
            <td>
                <a href="/edit-user?id=<?php echo $user['id']; ?>">Modifier</a>
                |
                <a href="/delete-user?id=<?php echo $user['id']; ?>" onclick="return confirm('Êtes-vous sûr?');">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<a href="/dashboard">Retour au tableau de bord</a>
