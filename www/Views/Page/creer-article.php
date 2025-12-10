<?php
// Retrieve category list
$categories = json_decode($categoriesJson ?? '[]', true);

// Retrieve already selected IDs (in case of form error)
// Expecting a JSON of IDs: e.g. "[1, 3]"
$selectedIds = json_decode($selectedCategoriesJson ?? '[]', true);
?>

<h2>Créer un nouvel Article</h2>

<?php if (!empty($message)): ?>
    <div style="color:red; border:1px solid red; padding:10px; margin-bottom:10px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form method="POST" action="">
    
    <div>
        <label>Titre</label>
        <input type="text" name="title" value="<?= htmlspecialchars($title ?? '') ?>" >
    </div>
    <div>
        <label>Slug</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($slug ?? '') ?>" >
    </div>
    
    <div>
        <label>Extrait</label>
        <input type="text" name="excerpt" value="<?= htmlspecialchars($excerpt ?? '') ?>">
    </div>

    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <label><strong>Catégories</strong></label>
        <br>
        <small style="color: grey;">Maintenez Ctrl (ou Cmd) pour en sélectionner plusieurs.</small>
        
        <select name="categories[]" multiple size="5" style="padding: 8px; width: 50%; margin-top: 5px;">
            <?php foreach ($categories as $cat): ?>
                <?php 
                    // Verify if category ID is in selected IDs array
                    // Assuming 'id_categorie' and 'nom' come directly from DB columns (kept in French)
                    $isSelected = in_array($cat['id_categorie'], $selectedIds) ? 'selected' : ''; 
                ?>
                <option value="<?= $cat['id_categorie'] ?>" <?= $isSelected ?>>
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <?php if (!empty($categoryError)): ?>
            <small style="color:red"><?= $categoryError ?></small>
        <?php endif; ?>

        <div style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px;">
            <label for="new_category">Ou créer une nouvelle catégorie :</label>
            <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px;">
                <input type="text" name="new_category" id="new_category" placeholder="Nom de la nouvelle catégorie" style="flex: 1; padding: 5px;">
                <button type="submit" name="action" value="add_category" style="padding: 5px 10px; cursor: pointer;">Ajouter</button>
            </div>
        </div>
    </div>

    <div >
        <label>Contenu</label>
        <textarea name="content" rows="5" style="width:100%"><?= htmlspecialchars($content ?? '') ?></textarea>
    </div>

    <div >
        <label><input type="checkbox" name="published" <?= ($publishedState ?? '') ?>> Publier</label>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top:10px;">Enregistrer l'Article</button>
</form>