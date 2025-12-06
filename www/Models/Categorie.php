<?php

namespace App\Models;

class Categorie
{
    private ?int $idCategorie = null;
    private string $nom = '';
    
    // Gestion des erreurs
    private array $erreurs = [];

    public function __construct(string $nom)
    {
        // Validation directe à l'instanciation
        $nomNettoye = trim($nom);
        
        if (empty($nomNettoye)) {
            $this->erreurs['nom'] = "Le nom de la catégorie est obligatoire.";
        } elseif (mb_strlen($nomNettoye) > 100) {
            $this->erreurs['nom'] = "Le nom est trop long (max 100 caractères).";
        }
        
        $this->nom = $nomNettoye;
    }

    public function getErreurs(): array
    {
        return $this->erreurs;
    }


    // --- Getters & Setters ---

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }


    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $nomNettoye = trim($nom);
        if (empty($nomNettoye)) {
            $this->erreurs['nom'] = "Le nom ne peut pas être vide.";
        } else {
            $this->nom = $nomNettoye;
            unset($this->erreurs['nom']);
        }
        return $this;
    }
}