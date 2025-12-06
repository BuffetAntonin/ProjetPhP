<?php
// On récupère la liste des catégories
$categories = json_decode($categories_json ?? '[]', true);

// On récupère les IDs déjà sélectionnés (en cas d'erreur formulaire)
// On s'attend à recevoir un JSON d'IDs : ex: "[1, 3]"
$selectedIds = json_decode($selected_categories_json ?? '[]', true);
?>

<h2>Créer un nouvel Article</h2>

<?php if (!empty($message)): ?>
    <div style="color:red; border:1px solid red; padding:10px; margin-bottom:10px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form method="POST" action="">
    
    <!-- Titre et Slug (Inchangé) -->
    <div>
        <label>Titre</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($titre ?? '') ?>" >
    </div>
    <div>
        <label>Slug</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($slug ?? '') ?>" >
    </div>
    
    <div>
        <label>Extrait</label>
        <input type="text" name="extrait" value="<?= htmlspecialchars($extrait ?? '') ?>">
    </div>

    <!-- SELECT MULTIPLE CATEGORIES -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <label><strong>Catégories</strong></label>
        <br>
        <small style="color: grey;">Maintenez Ctrl (ou Cmd) pour en sélectionner plusieurs.</small>
        
        <!-- Liste déroulante -->
        <select name="categories[]" multiple size="5" style="padding: 8px; width: 50%; margin-top: 5px;">
            <?php foreach ($categories as $cat): ?>
                <?php 
                    // On vérifie si l'ID de la catégorie est dans le tableau des IDs sélectionnés
                    $isSelected = in_array($cat['id_categorie'], $selectedIds) ? 'selected' : ''; 
                ?>
                <option value="<?= $cat['id_categorie'] ?>" <?= $isSelected ?>>
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <?php if (!empty($error_categorie)): ?>
            <small style="color:red"><?= $error_categorie ?></small>
        <?php endif; ?>

        <!-- Option Full PHP : Création à la volée lors de la soumission globale -->
        <div style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px;">
            <label for="nouvelle_categorie">Ou créer une nouvelle catégorie :</label>
            <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px;">
                <input type="text" name="nouvelle_categorie" id="nouvelle_categorie" placeholder="Nom de la nouvelle catégorie" style="flex: 1; padding: 5px;">
                <button type="submit" name="action" value="ajouter_categorie" style="padding: 5px 10px; cursor: pointer;">Ajouter</button>
            </div>
        </div>
    </div>

    <!-- Contenu -->
    <div >
        <label>Contenu</label>
        <textarea name="contenu" rows="5" style="width:100%"><?= htmlspecialchars($contenu ?? '') ?></textarea>
    </div>

    <div >
        <label><input type="checkbox" name="publie" <?= ($publie_etat ?? '') ?>> Publier</label>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top:10px;">Enregistrer l'Article</button>
</form>