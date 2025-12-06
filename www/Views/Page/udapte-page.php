<h2>Modifier la page #<?= $id_page ?></h2>

<!-- Message d'erreur local (si validation échoue) -->
<?php if (!empty($message)): ?>
    <div style="color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin-bottom: 15px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Le formulaire poste sur l'URL actuelle (donc /page/edit?id=X) -->
<form method="POST" action="">
    
    <!-- TITRE -->
    <div class="form-group">
        <label>Titre :</label><br>
        <input type="text" name="titre" value="<?= htmlspecialchars($titre) ?>">
        <?php if (!empty($error_titre)): ?>
            <small style="color:red"><?= $error_titre ?></small>
        <?php endif; ?>
    </div>
    <br>

    <!-- SLUG -->
    <div class="form-group">
        <label>Slug (URL) :</label><br>
        <input type="text" name="slug" value="<?= htmlspecialchars($slug) ?>">
        <?php if (!empty($error_slug)): ?>
            <small style="color:red"><?= $error_slug ?></small>
        <?php endif; ?>
    </div>
    <br>

    <!-- CONTENU -->
    <div class="form-group">
        <label>Contenu :</label><br>
        <textarea name="contenu" rows="10" ><?= htmlspecialchars($contenu) ?></textarea>
        <?php if (!empty($error_contenu)): ?>
            <small style="color:red"><?= $error_contenu ?></small>
        <?php endif; ?>
    </div>
    <br>

    <!-- PUBLICATION -->
    <div class="form-group">
        <label>
            <input type="checkbox" name="publie" <?= $publie_etat ?>> 
            Publier cette page ?
        </label>
    </div>
    <br>

    <!-- BOUTONS -->
    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; background: blue; color: white; border: none; cursor: pointer;">
        Enregistrer les modifications
    </button>
    
    <a href="/index-page" style="margin-left: 20px; color: grey;">Annuler</a>
</form>