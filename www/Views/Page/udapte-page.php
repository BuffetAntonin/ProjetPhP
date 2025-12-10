<h2>Modifier la page #<?= $page_id ?></h2>

<?php if (!empty($message)): ?>
    <div style="color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin-bottom: 15px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form method="POST" action="">
    
    <div class="form-group">
        <label>Titre :</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($title) ?>">
        <?php if (!empty($error_title)): ?>
            <small style="color:red"><?= $error_title ?></small>
        <?php endif; ?>
    </div>
    <br>

    <div class="form-group">
        <label>Slug (URL) :</label><br>
        <input type="text" name="slug" value="<?= htmlspecialchars($slug) ?>">
        <?php if (!empty($error_slug)): ?>
            <small style="color:red"><?= $error_slug ?></small>
        <?php endif; ?>
    </div>
    <br>

    <div class="form-group">
        <label>Contenu :</label><br>
        <textarea name="content" rows="10"><?= htmlspecialchars($content) ?></textarea>
        <?php if (!empty($error_content)): ?>
            <small style="color:red"><?= $error_content ?></small>
        <?php endif; ?>
    </div>
    <br>

    <div class="form-group">
        <label>
            <input type="checkbox" name="published" <?= $published_state ?>> 
            Publier cette page ?
        </label>
    </div>
    <br>

    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; background: blue; color: white; border: none; cursor: pointer;">
        Enregistrer les modifications
    </button>
    
    <a href="/index-page" style="margin-left: 20px; color: grey;">Annuler</a>
</form>