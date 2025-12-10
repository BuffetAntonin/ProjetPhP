
<?php 
    $mesPages = [];
    if(isset($pages_json)) {
        $mesPages = json_decode($pages_json, true); 
    }
?>

<nav>
    <h3>Menu</h3>
    <ul>
        <?php foreach($mesPages as $page): ?>
            <li>
                <a href="/<?= htmlspecialchars($page['slug']) ?>">
                    <?= htmlspecialchars($page['titre']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<?php

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo '<a href="/login">connexion</a>';
} else {
    echo '<a href="/dashboard">dashboard</a>';
}
?>