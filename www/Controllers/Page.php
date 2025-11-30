<?php

namespace App\Controllers; 

use App\Repository\PageRepository;
use App\Models\Page;

class PageController
{

    public function creer(): void
    {
        $message = "";
        $succes = false;

        // 1. Gestion de la soumission du formulaire (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Simulation ID User (à remplacer par $_SESSION['user']['id'])
            $idUtilisateur = 1; 

            // Création de l'objet (la validation est faite par le constructeur de Page)
            // On utilise null coalescing operator (??) pour éviter les warnings
            $page = new Page(
                $_POST['titre'] ?? '',
                $_POST['slug'] ?? '',
                $_POST['contenu'] ?? '',
                $idUtilisateur,
                isset($_POST['publie'])
            );

        } else {
            // 2. Affichage initial (GET) -> Objet vide
            $page = new Page('', '', '', 1);
        }
    }
}