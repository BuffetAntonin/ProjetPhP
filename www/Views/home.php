
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

<a href="/login">connexion</a>