<?php

namespace App\Controllers;

use App\Core\Render;
use App\Repository\PageRepository;

class Base
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
        $publishedPages = $this->pageRepository->findAllPublished();

        $pagesList = [];
        
        foreach($publishedPages as $page) {
            $pagesList[] = [
                'title' => $page->getTitle(), 
                'slug'  => $page->getSlug()
            ];
        }

        $render = new \App\Core\Render("home", "frontoffice");
        
        $render->assign('pages_json', json_encode($pagesList));
        
        $render->render();
    }

    public function showPage(string $slug): void
    {
        $page = $this->pageRepository->findBySlug($slug);

        if(!$page) {
            die("Erreur 404 : Cette page n'existe pas."); 
        }

        $render = new Render("default_page", "frontoffice");
        
        $render->assign("title", $page->getTitle());
        $render->assign("content", $page->getContent());
        
        $render->render();
    }

    public function portfolio(): void
    {
        echo "Base portfolio";
    }
}