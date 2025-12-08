<h2>Créer une nouvelle page</h2>

<?php if (!empty($message)): ?>
    <div>
        <?= $message ?>
    </div>
<?php endif; ?>

<form method="POST" action="/cree-page">
    
    <div class="form-group">
        <label>Titre</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($titre) ?>">
        
        <?php if (!empty($error_titre)): ?>
            <small class="error" style="color:red"><?= $error_titre ?></small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Slug (URL)</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($slug) ?>">
        <?php if (!empty($error_slug)): ?>
            <small class="error" style="color:red"><?= $error_slug ?></small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Contenu</label>
        <textarea name="contenu" rows="5"><?= htmlspecialchars($contenu) ?></textarea>
        <?php if (!empty($error_contenu)): ?>
            <small class="error" style="color:red"><?= $error_contenu ?></small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="publie" <?= $publie_etat ?>> Publier immédiatement
        </label>
    </div>
    
    <button type="submit">Enregistrer</button>
    <a href="/index-page">Liste des pages</a>
</form>