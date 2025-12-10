<?php if ($error): ?>
    <div style="color: red; padding: 15px; border: 2px solid red; margin-bottom: 20px; background-color: #ffe6e6;">
        <h3>Erreur</h3>
        <p><?php echo htmlspecialchars($error); ?></p>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color: green; padding: 15px; border: 2px solid green; margin-bottom: 20px; background-color: #e6ffe6;">
        <h3>Succès</h3>
        <p><?php echo htmlspecialchars($success); ?></p>
    </div>
<?php endif; ?>

<br>
<a href="/users-management">Retour à la gestion des utilisateurs</a>
