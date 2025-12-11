<?php 
    $myPages = [];
    // On récupère le JSON envoyé par le contrôleur
    if(isset($pages_json)) {
        $myPages = json_decode($pages_json, true); 
    }
?>

<nav>
    <h3>Menu</h3>
    <ul>
        <?php foreach($myPages as $page): ?>
            <li>
                <a href="/<?= htmlspecialchars(urlencode($page['slug'])) ?>">
                    <?= htmlspecialchars($page['title']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<?php
// Vérification de la session
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo '<a href="/login">connexion</a>';
} else {
    echo '<a href="/dashboard">dashboard</a>';
}
?>