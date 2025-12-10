<h2>Créer une nouvelle page</h2>

<?php if (!empty($message)): ?>
    <div>
        <?= $message ?>
    </div>
<?php endif; ?>

<form method="POST" action="/cree-page">
    
    <div class="form-group">
        <label>Titre</label>
        <input type="text" name="title" value="<?= htmlspecialchars($title) ?>">
        
        <?php if (!empty($error_title)): ?>
            <small class="error" style="color:red"><?= $error_title ?></small>
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
        <textarea name="content" rows="5"><?= htmlspecialchars($content) ?></textarea>
        <?php if (!empty($error_content)): ?>
            <small class="error" style="color:red"><?= $error_content ?></small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="published" <?= $published_state ?>> Publier immédiatement
        </label>
    </div>
    
    <button type="submit">Enregistrer</button>
    <a href="/index-page">Liste des pages</a>
</form>