<?php

namespace App\Controllers;

use Exception;
use App\Core\Render;
use App\Models\Page;
use App\Repository\PageRepository;

class PageController
{
    private PageRepository $repo;

    public function __construct()
    {
        // RESTART SESSION (Essential for Flash messages)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->repo = PageRepository::getInstance();
    }

    /**
     * Displays the list of pages
     */
    public function index(): void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: /'); 
            exit; 
        }
        
        $userId = $_SESSION['user_id'];

        // 1. Fetch data
        $pageObjects = $this->repo->findAllByUserId($userId);

        $pagesArray = [];
        foreach($pageObjects as $p) {
            // Mapping object to array with English keys
            $pagesArray[] = [
                'id' => $p->getId(),             // Updated method name
                'title' => $p->getTitle(),       // Updated method name
                'slug' => $p->getSlug(),
                'is_published' => $p->isPublished() // Updated method name
            ];
        }

        $render = new Render('page/index', 'backoffice');
        
        $flashMessage = '';
        $flashType = '';

        if (isset($_SESSION['flash_success'])) {
            $flashMessage = $_SESSION['flash_success'];
            $flashType = 'success';
            unset($_SESSION['flash_success']); // Remove after display
        } elseif (isset($_SESSION['flash_error'])) {
            $flashMessage = $_SESSION['flash_error'];
            $flashType = 'error';
            unset($_SESSION['flash_error']);
        }

        $render->assign('flash_message', $flashMessage);
        $render->assign('flash_type', $flashType);
        $render->assign('pages_json', json_encode($pagesArray));
        
        $render->render();
    }

    /**
     * Handle insertion
     */
    public function insert(): void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: /'); 
            exit; 
        }

        // Renamed view file suggestion: 'page/create' instead of 'creer-page'
        $render = new Render('page/creer-page', 'backoffice');

        $title = ''; $slug = ''; $content = ''; $isPublished = false;
        $message = ""; 
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id']; 
            
            // Assuming HTML form inputs are now named 'title', 'slug', 'content', 'published'
            $title = $_POST['title'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $content = $_POST['content'] ?? '';
            $isPublished = isset($_POST['published']);

            // Constructor uses the new English variable names
            $page = new Page($title, $slug, $content, $userId, $isPublished);

            if (empty($page->getErrors())) {
                try {
                    $success = $this->repo->insertPage($page);

                    if ($success) {
                        // SUCCESS
                        $_SESSION['flash_success'] = "✅ La page a été créée avec succès !";
                        header('Location: /index-page');
                        exit;
                    }
                } catch (Exception $e) {
                    $message = "Erreur : " . $e->getMessage();
                } 
            } else {
                $message = "Veuillez corriger les erreurs ci-dessous.";
                $errors = $page->getErrors();
            }
        }

        // Sending English variable names to the view
        $render->assign('title', $title);
        $render->assign('slug', $slug);
        $render->assign('content', $content);
        $render->assign('published_state', $isPublished ? 'checked' : '');
        $render->assign('message', $message);
        
        // Error keys match the Model (title, slug, content)
        $render->assign('error_title',   $errors['title'] ?? '');
        $render->assign('error_slug',    $errors['slug'] ?? '');
        $render->assign('error_content', $errors['content'] ?? '');

        $render->render();
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
        $currentUserId = $_SESSION['user_id'];
        $isAdmin = $this->repo->isAdmin($currentUserId);   

        $page = $this->repo->findById($pageId, $currentUserId);

        if (!$page) {
            $_SESSION['flash_error'] = "Page introuvable.". $pageId." ".$currentUserId;
            header('Location: /index-page');
            exit;
        }

        if ($page->isPublished()) {
            // ERROR: Page is published
            $_SESSION['flash_error'] = "Impossible de supprimer une page publiée. Dépubliez-la d'abord.";
        } 
        elseif ($page->getUserId() !== $currentUserId && !$isAdmin) {
            // ERROR: Not owner
            $_SESSION['flash_error'] = "Action interdite : Vous n'êtes pas le propriétaire.";
        } 
        else {
            // SUCCESS
            $this->repo->deletePage($pageId);
            $_SESSION['flash_success'] = "Page supprimée avec succès.";
        }
        
        header('Location: /index-page');
        exit;
    }

    public function publication(): void
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
        $currentUserId = $_SESSION['user_id'];
        $isAdmin = $this->repo->isAdmin($currentUserId);

        $page = $this->repo->findById($pageId, $currentUserId);

        if (!$page) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }
        elseif ($page->getUserId() !== $currentUserId && !$isAdmin) {
            // ERROR: Not owner
            $_SESSION['flash_error'] = "Action interdite : Vous n'êtes pas le propriétaire.";
        } 
        else {
            // SUCCESS
            $this->repo->updateStatus($pageId);
            $_SESSION['flash_success'] = "Page publiée/dépubliée avec succès.";
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
        
        // 1. Check ID in URL
        if (!isset($_GET['id'])) {
            $_SESSION['flash_error'] = "ID manquant.";
            header('Location: /index-page');
            exit;
        }

        $pageId = (int)$_GET['id'];
        $currentUserId = $_SESSION['user_id'];
        
        // 2. Fetch existing page from DB
        $existingPage = $this->repo->findById($pageId, $currentUserId);

        if (!$existingPage) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }

        // --- Init variables for view ---
        $title = $existingPage->getTitle();
        $slug = $existingPage->getSlug();
        $content = $existingPage->getContent();
        $isPublished = $existingPage->isPublished();
        
        $message = "";
        $errors = [];

        // 3. HANDLE FORM (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Overwrite variables with user input
            $title = $_POST['title'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $content = $_POST['content'] ?? '';
            $isPublished = isset($_POST['published']);

            // Create Page object to validate
            $updatedPage = new Page($title, $slug, $content, $existingPage->getUserId(), $isPublished);
            
            // IMPORTANT: Set ID so Repository knows which one to update
            $updatedPage->setId($pageId);

            if (empty($updatedPage->getErrors())) {
                try {
                    $this->repo->updatePage($updatedPage);

                    // Success -> Redirect
                    $_SESSION['flash_success'] = "✅ Page modifiée avec succès !";
                    header('Location: /index-page');
                    exit;

                } catch (Exception $e) {
                    $message = "Erreur : " . $e->getMessage();
                }
            } else {
                $message = "Veuillez corriger les erreurs.";
                $errors = $updatedPage->getErrors();
            }
        }

        $render = new Render('page/udapte-page', 'backoffice');

        $render->assign('page_id', (string)$pageId); 
        
        $render->assign('title', $title);
        $render->assign('slug', $slug);
        $render->assign('content', $content);
        $render->assign('published_state', $isPublished ? 'checked' : '');
        
        $render->assign('message', $message);
        $render->assign('error_title',   $errors['title'] ?? '');
        $render->assign('error_slug',    $errors['slug'] ?? '');
        $render->assign('error_content', $errors['content'] ?? '');

        $render->render();
    }

    
}