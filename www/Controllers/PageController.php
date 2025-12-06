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
        // ON REMET LA SESSION (Indispensable pour les messages Flash)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->repo = PageRepository::getInstance();
    }

    /**
     * Affiche la liste des pages
     */
    public function index(): void
    {
        $idUtilisateur = 1;

        // 1. Récupération des données
        $pagesObjects = $this->repo->findAllByUserId($idUtilisateur);

        $pagesArray = [];
        foreach($pagesObjects as $p) {
            $pagesArray[] = [
                'id' => $p->getIdPage(),
                'titre' => $p->getTitre(),
                'slug' => $p->getSlug(),
                'est_publie' => $p->isEstPublie()
            ];
        }

        $render = new Render('page/index', 'backoffice');
        
        // --- GESTION DES MESSAGES FLASH VIA SESSION ---
        $flashMessage = '';
        $flashType = '';

        if (isset($_SESSION['flash_success'])) {
            $flashMessage = $_SESSION['flash_success'];
            $flashType = 'success';
            unset($_SESSION['flash_success']); // On supprime pour qu'il ne s'affiche qu'une fois
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
     * Traitement de l'insertion
     */
    public function insert(): void
    {
        $render = new Render('page/creer-page', 'backoffice');

        $titre = ''; $slug = ''; $contenu = ''; $estPublie = false;
        $message = ""; 
        $erreurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUtilisateur = 1; 
            
            $titre = $_POST['titre'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $contenu = $_POST['contenu'] ?? '';
            $estPublie = isset($_POST['publie']);

            $page = new Page($titre, $slug, $contenu, $idUtilisateur, $estPublie);

            if (empty($page->getErreurs())) {
                try {
                    $succes = $this->repo->insertPage($page);

                    if ($succes) {
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
                $erreurs = $page->getErreurs();
            }
        }

        $render->assign('titre', $titre);
        $render->assign('slug', $slug);
        $render->assign('contenu', $contenu);
        $render->assign('publie_etat', $estPublie ? 'checked' : '');
        $render->assign('message', $message);
        $render->assign('error_titre',   $erreurs['titre'] ?? '');
        $render->assign('error_slug',    $erreurs['slug'] ?? '');
        $render->assign('error_contenu', $erreurs['contenu'] ?? '');

        $render->render();
    }

    /**
     * Suppression (Avec Session et vérifications)
     */
    public function delete(): void
    {
        if (!isset($_GET['id'])) {
            $_SESSION['flash_error'] = "ID de page manquant.";
            header('Location: /index-page');
            exit;
        }

        $idPage = (int)$_GET['id'];
        $idUserConnecte = 1; 
        $estAdmin = false;   

        $page = $this->repo->findById($idPage);

        if (!$page) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }

        if ($page->isEstPublie()) {
            // ERREUR : Page publiée
            $_SESSION['flash_error'] = "Impossible de supprimer une page publiée. Dépubliez-la d'abord.";
        } 
        elseif ($page->getIdUtilisateur() !== $idUserConnecte && !$estAdmin) {
            // ERREUR : Pas propriétaire
            $_SESSION['flash_error'] = "Action interdite : Vous n'êtes pas le propriétaire.";
        } 
        else {
            // SUCCÈS
            $this->repo->deletePage($idPage);
            $_SESSION['flash_success'] = "Page supprimée avec succès.";
        }
        
        header('Location: /index-page');
        exit;
    }

    public function publication():void
    {
        if (!isset($_GET['id'])) {
            $_SESSION['flash_error'] = "ID de page manquant.";
            header('Location: /index-page');
            exit;
        }

        $idPage = (int)$_GET['id'];
        $idUserConnecte = 1; 
        $estAdmin = false;   

        $page = $this->repo->findById($idPage);

        if (!$page) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }
        elseif ($page->getIdUtilisateur() !== $idUserConnecte && !$estAdmin) {
            // ERREUR : Pas propriétaire
            $_SESSION['flash_error'] = "Action interdite : Vous n'êtes pas le propriétaire.";
        } 
        else {
            // SUCCÈS
            $this->repo->updateStatus($idPage);
            $_SESSION['flash_success'] = "Page publier/Dépublier avec succès.";
        }
        
        header('Location: /index-page');
        exit;
    }

    public function update(): void
    {
        // 1. Vérification de l'ID dans l'URL (ex: /page/edit?id=12)
        if (!isset($_GET['id'])) {
            $_SESSION['flash_error'] = "ID manquant.";
            header('Location: /index-page');
            exit;
        }

        $idPage = (int)$_GET['id'];
        
        // 2. On récupère la page existante en BDD
        $existingPage = $this->repo->findById($idPage);

        if (!$existingPage) {
            $_SESSION['flash_error'] = "Page introuvable.";
            header('Location: /index-page');
            exit;
        }

        // --- Initialisation des variables pour la vue ---
        // Par défaut, on pré-remplit avec les données de la base
        $titre = $existingPage->getTitre();
        $slug = $existingPage->getSlug();
        $contenu = $existingPage->getContenu();
        $estPublie = $existingPage->isEstPublie();
        
        $message = "";
        $erreurs = [];

        // 3. TRAITEMENT DU FORMULAIRE (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // On écrase les variables avec ce que l'utilisateur vient de taper
            $titre = $_POST['titre'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $contenu = $_POST['contenu'] ?? '';
            $estPublie = isset($_POST['publie']);

            // On crée un objet Page avec les nouvelles données pour valider
            // (On garde le même ID utilisateur que l'original)
            $pageUpdate = new Page($titre, $slug, $contenu, $existingPage->getIdUtilisateur(), $estPublie);
            
            // IMPORTANT : On doit remettre l'ID de la page pour que le Repository sache laquelle modifier
            $pageUpdate->setIdPage($idPage);

            if (empty($pageUpdate->getErreurs())) {
                try {
                    $this->repo->updatePage($pageUpdate);

                    // Succès -> Redirection
                    $_SESSION['flash_success'] = "✅ Page modifiée avec succès !";
                    header('Location: /index-page');
                    exit;

                } catch (Exception $e) {
                    $message = "Erreur : " . $e->getMessage();
                }
            } else {
                $message = "Veuillez corriger les erreurs.";
                $erreurs = $pageUpdate->getErreurs();
            }
        }

        // 4. PRÉPARATION DE LA VUE
        // On utilise un fichier dédié 'modifier.php' (ou on pourrait réutiliser creer.php)
        $render = new Render('page/udapte-page', 'backoffice');

        // On passe l'ID pour que le formulaire sache où poster (ou pour le lien retour)
        // (string) car Render est strict
        $render->assign('id_page', (string)$idPage); 
        
        $render->assign('titre', $titre);
        $render->assign('slug', $slug);
        $render->assign('contenu', $contenu);
        $render->assign('publie_etat', $estPublie ? 'checked' : '');
        
        $render->assign('message', $message);
        $render->assign('error_titre',   $erreurs['titre'] ?? '');
        $render->assign('error_slug',    $erreurs['slug'] ?? '');
        $render->assign('error_contenu', $erreurs['contenu'] ?? '');

        $render->render();
    }
}