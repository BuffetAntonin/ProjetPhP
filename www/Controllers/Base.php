<?php

namespace App\Controllers;

use App\Core\Render;
use App\Repository\PageRepository;

class Base
{
    private PageRepository $repoPages;

    public function __construct()
    {
        // ON REMET LA SESSION (Indispensable pour les messages Flash)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->repoPages = PageRepository::getInstance();
    }

    public function index(): void
    {

        // Correction : utiliser $this->repoPages pour accéder à la propriété de la classe
        $pagesPubliees = $this->repoPages->findAllPublished();

        // 2. Conversion des objets en tableau simple (pour le JSON)
        $pagesArray = [];
        foreach($pagesPubliees as $p) {
            $pagesArray[] = [
                'titre' => $p->getTitre(),
                'slug'  => $p->getSlug()
            ];
        }

        $render = new \App\Core\Render("home", "frontoffice");
        
        // 3. On encode en JSON (cela devient une string, donc assign() est content)
        $render->assign('pages_json', json_encode($pagesArray));
        
        $render->render();
    }

    public function showPage(string $slug): void
    {
        $page = $this->repoPages->findBySlug($slug);

        if(!$page) {
            die("Erreur 404 : Cette page n'existe pas."); 
        }

        $render = new Render("default_page", "frontoffice");
        $render->assign("titre", $page->getTitre());
        $render->assign("contenu", $page->getContenu());
        
        $render->render();
    }


    public function portfolio(): void
    {
        echo "Base portfolio";
    }

}