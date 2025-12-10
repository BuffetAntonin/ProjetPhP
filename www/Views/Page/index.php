<?php

// 1. On décode la string JSON reçue du contrôleur
$pages = json_decode($pages_json ?? '[]', true);
?>

<!-- Affichage des Messages Flash (Succès ou Erreur) -->
<?php if (!empty($flash_message)): ?>
    <?php 
        // On choisit la couleur : Rouge si erreur, Vert si succès
        $isError = (isset($flash_type) && $flash_type === 'error');
        $style = $isError 
            ? 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' // Rouge
            : 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'; // Vert
    ?>
    <div style="<?= $style ?> padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center;">
        <?= htmlspecialchars($flash_message) ?>
    </div>
<?php endif; ?>

<div class="dashboard-header">
    <h1>Mes Pages</h1>
    
    <div class="actions">
        <a href="/cree-page" class="btn btn-primary">
            + Créer une Page
        </a>
    </div>
</div>

<table class="table-pages">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Slug (URL)</th>
            <th>État</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($pages)): ?>
            <tr>
                <td colspan="4" class="empty-message" style="text-align:center; padding:20px;">
                    Aucune page trouvée. Créez-en une !
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($pages as $page): ?>
                <tr>
                    <td>
                        <!-- Valeur invisible contenant l'ID de la page -->
                        <input type="hidden" class="page-id-hidden" value="<?= $page['id'] ?>">
                        <strong><?= htmlspecialchars($page['titre']) ?></strong>
                    </td>
                    <td>
                        <small>/<?= htmlspecialchars($page['slug']) ?></small>
                    </td>
                    <td>
                        <?php if ($page['est_publie']): ?>
                            <span style="color:green; font-weight:bold;">Publié</span>
                        <?php else: ?>
                            <span style="color:orange; font-weight:bold;">Brouillon</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        &nbsp;
                        <!-- Bouton Publier/Dépublier -->
                        <form action="/publication" method="get" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?= $page['id'] ?>">
                            <button type="submit" class="btn-action btn-delete" style="color:blue; cursor:pointer; background:none; border:none; padding:0; font:inherit; text-decoration: underline;">
                                <?= $page['est_publie'] ? 'Dépublier' : 'Publier' ?>
                            </button>
                        </form>
                        &nbsp;&nbsp;
                        <!-- Bouton Modifier -->
                        <form action="/update-page" method="get" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?= $page['id'] ?>">
                            <button type="submit" class="btn-action btn-delete" style="color:blue; cursor:pointer; background:none; border:none; padding:0; font:inherit; text-decoration: underline;">
                                Modifier
                            </button>
                        </form>
                        &nbsp;&nbsp;
                        
                        <!-- FORMULAIRE DE SUPPRESSION -->
                        <form action="/supprime-page" method="get" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?= $page['id'] ?>">
                            <button type="submit" class="btn-action btn-delete" style="color:red; cursor:pointer; background:none; border:none; padding:0; font:inherit; text-decoration: underline;">
                                Supprimer
                            </button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<a href="/">Accueil</a>
<br>
<a href="/dashboard">dashboard</a>