<?php

namespace App\Controllers;

use Exception;
use App\Core\Render;
use App\Models\Page;
use App\Repository\ArticleRepository;

class ArticleController
{
    private ArticleRepository $repo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->repo = ArticleRepository::getInstance();
    }

    public function insert(): void
    {
        $titre = ''; $slug = ''; $contenu = ''; $idCat = 0; $publie = false;
        $message = ''; $erreurs = [];

        // 1. On récupère la liste des catégories pour le <select>
        $categories = $this->repo->findAllCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = $_POST['titre'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $contenu = $_POST['contenu'] ?? '';
            $idCat = (int)($_POST['id_categorie'] ?? 0);
            $publie = isset($_POST['publie']);

            $article = new Article($titre, $slug, $contenu, 1, $idCat, $publie);

            if (empty($article->getErreurs())) {
                try {
                    $this->repo->insertArticle($article);
                    $_SESSION['flash_success'] = "Article créé !";
                    header('Location: /index-page');
                    exit;
                } catch (\Exception $e) {
                    $message = "Erreur SQL : " . $e->getMessage();
                }
            } else {
                $erreurs = $article->getErreurs();
                $message = "Corrigez les erreurs.";
            }
        }

        $render = new Render('page/creer-article', 'backoffice');
        
        // On passe les données simples
        $render->assign('titre', $titre);
        $render->assign('slug', $slug);
        $render->assign('contenu', $contenu);
        $render->assign('id_categorie_selected', (string)$idCat); // Pour remettre la sélection
        $render->assign('publie_etat', $publie ? 'checked' : '');
        $render->assign('message', $message);
        $render->assign('error_categorie', $erreurs['categorie'] ?? '');

        // ASTUCE : On passe la liste des catégories en JSON car Render n'accepte que des strings
        $render->assign('categories_json', json_encode($categories));

        $render->render();
    }
}