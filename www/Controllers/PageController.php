<?php

namespace App\Controllers;

use Exception;
use App\Core\Render;
use App\Models\Page;
use App\Repository\PageRepository;

class PageController
{
    private PageRepository $pageRepository;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->pageRepository = PageRepository::getInstance();
    }

    public function index(): void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: /'); 
            exit; 
        }
        
        $userId = $_SESSION['user_id'];

        $pageObjects = $this->pageRepository->findAllByUserId($userId);

        $pagesList = [];
        foreach($pageObjects as $page) {
            // Les clés ('id', 'titre'...) restent en français car la vue les attend probablement ainsi
            $pagesList[] = [
                'id' => $page->getIdPage(),
                'titre' => $page->getTitre(),
                'slug' => $page->getSlug(),
                'est_publie' => $page->isEstPublie()
            ];
        }

        $renderer = new Render('page/index', 'backoffice');
        
        $flashMessage = '';
        $flashType = '';

        if (isset($_SESSION['flash_success'])) {
            $flashMessage = $_SESSION['flash_success'];
            $flashType = 'success';
            unset($_SESSION['flash_success']);
        } elseif (isset($_SESSION['flash_error'])) {
            $flashMessage = $_SESSION['flash_error'];
            $flashType = 'error';
            unset($_SESSION['flash_error']);
        }

        $renderer->assign('flash_message', $flashMessage);
        $renderer->assign('flash_type', $flashType);
        $renderer->assign('pages_json', json_encode($pagesList));
        
        $renderer->render();
    }

    public function insert(): void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: /'); 
            exit; 
        }
        
        $renderer = new Render('page/creer-page', 'backoffice');

        $title = ''; 
        $slug = ''; 
        $content = ''; 
        $isPublished = false;
        
        $message = ""; 
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id']; 
            
            // On garde les clés $_POST en français car elles viennent du formulaire HTML
            $title = $_POST['titre'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $content = $_POST['contenu'] ?? '';
            $isPublished = isset($_POST['publie']);

            $page = new Page($title, $slug, $content, $userId, $isPublished);

            if (empty($page->getErreurs())) {
                try {
                    $success = $this->pageRepository->insertPage($page);

                    if ($success) {
                        // SUCCÈS : On utilise la SESSION
                        $_SESSION['flash_success'] = "✅ La page a été créée avec succès !";
                        header('Location: /index-page');
                        exit;
                    }
                } catch (Exception $e) {
                    $message = "Erreur : " . $e->getMessage();
                } 
            } else {
                $message = "Veuillez corriger les erreurs ci-dessous.";
                $errors = $page->getErreurs();
            }
        }

        // On garde les clés d'assignation en français pour la compatibilité avec la vue
        $renderer->assign('titre', $title);
        $renderer->assign('slug', $slug);
        $renderer->assign('contenu', $content);
        $renderer->assign('publie_etat', $isPublished ? 'checked' : '');
        $renderer->assign('message', $message);
        
        $renderer->assign('error_titre',   $errors['titre'] ?? '');
        $renderer->assign('error_slug',    $errors['slug'] ?? '');
        $renderer->assign('error_contenu', $errors['contenu'] ?? '');

        $renderer->render();
    }

    public function delete(): void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: /'); 
            exit; 
        }

        if (!isset($_GET['id'])) {
            $_SESSION['flash_error'] = "ID de page manquant.";
            header('Location: /index-page');
            exit;
        }

        $pageId = (int)$_GET['id'];
        $currentUserId = $_SESSION['user_id'];; 
        $isAdmin = false;   

        $page = $this->pageRepository->findById($pageId);

        if (!$page) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }

        if ($page->isEstPublie()) {
            // ERREUR : Page publiée
            $_SESSION['flash_error'] = "Impossible de supprimer une page publiée. Dépubliez-la d'abord.";
        } 
        elseif ($page->getIdUtilisateur() !== $currentUserId && !$isAdmin) {
            // ERREUR : Pas propriétaire
            $_SESSION['flash_error'] = "Action interdite : Vous n'êtes pas le propriétaire.";
        } 
        else {
            // SUCCÈS
            $this->pageRepository->deletePage($pageId);
            $_SESSION['flash_success'] = "Page supprimée avec succès.";
        }
        
        header('Location: /index-page');
        exit;
    }

    public function publication():void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: /'); 
            exit; 
        }

        if (!isset($_GET['id'])) {
            $_SESSION['flash_error'] = "ID de page manquant.";
            header('Location: /index-page');
            exit;
        }

        $pageId = (int)$_GET['id'];
        $currentUserId = $_SESSION['user_id'];; 
        $isAdmin = false;   

        $page = $this->pageRepository->findById($pageId);

        if (!$page) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }
        elseif ($page->getIdUtilisateur() !== $currentUserId && !$isAdmin) {
            // ERREUR : Pas propriétaire
            $_SESSION['flash_error'] = "Action interdite : Vous n'êtes pas le propriétaire.";
        } 
        else {
            // SUCCÈS
            $this->pageRepository->updateStatus($pageId);
            $_SESSION['flash_success'] = "Page publier/Dépublier avec succès.";
        }
        
        header('Location: /index-page');
        exit;
    }

    public function update(): void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: /'); 
            exit; 
        }

        if (!isset($_GET['id'])) {
            $_SESSION['flash_error'] = "ID manquant.";
            header('Location: /index-page');
            exit;
        }
        $userId = $_SESSION['user_id']; 

        $pageId = (int)$_GET['id'];
        
        $existingPage = $this->pageRepository->findById($pageId,$userId);

        if (!$existingPage) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }

        // --- Initialisation des variables pour la vue ---
        // Par défaut, on pré-remplit avec les données de la base
        $title = $existingPage->getTitre();
        $slug = $existingPage->getSlug();
        $content = $existingPage->getContenu();
        $isPublished = $existingPage->isEstPublie();
        
        $message = "";
        $errors = [];

        // 3. TRAITEMENT DU FORMULAIRE (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // On écrase les variables avec ce que l'utilisateur vient de taper
            $title = $_POST['titre'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $content = $_POST['contenu'] ?? '';
            $isPublished = isset($_POST['publie']);

            // On crée un objet Page avec les nouvelles données pour valider
            // (On garde le même ID utilisateur que l'original)
            $pageUpdate = new Page($title, $slug, $content, $existingPage->getIdUtilisateur(), $isPublished);
            
            // IMPORTANT : On doit remettre l'ID de la page pour que le Repository sache laquelle modifier
            $pageUpdate->setIdPage($pageId);

            if (empty($pageUpdate->getErreurs())) {
                try {
                    $this->pageRepository->updatePage($pageUpdate);

                    // Succès -> Redirection
                    $_SESSION['flash_success'] = "✅ Page modifiée avec succès !";
                    header('Location: /index-page');
                    exit;

                } catch (Exception $e) {
                    $message = "Erreur : " . $e->getMessage();
                }
            } else {
                $message = "Veuillez corriger les erreurs.";
                $errors = $pageUpdate->getErreurs();
            }
        }

        // 4. PRÉPARATION DE LA VUE
        $renderer = new Render('page/udapte-page', 'backoffice');

        // On passe l'ID pour que le formulaire sache où poster (ou pour le lien retour)
        // (string) car Render est strict
        $renderer->assign('id_page', (string)$pageId); 
        
        $renderer->assign('titre', $title);
        $renderer->assign('slug', $slug);
        $renderer->assign('contenu', $content);
        $renderer->assign('publie_etat', $isPublished ? 'checked' : '');
        
        $renderer->assign('message', $message);
        $renderer->assign('error_titre',   $errors['titre'] ?? '');
        $renderer->assign('error_slug',    $errors['slug'] ?? '');
        $renderer->assign('error_contenu', $errors['contenu'] ?? '');

        $renderer->render();
    }

}