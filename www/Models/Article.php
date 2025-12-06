<?php

namespace App\Models;

use DateTime;

class Article
{
    // Propriétés de la BDD
    private ?int $idArticle = null;
    private string $titre = '';
    private string $slug = '';
    private string $extrait = ''; // Spécifique Article
    private string $contenu = '';
    private DateTime $datePublication; // Remplace dateCreation pour les articles
    private DateTime $dateModification;
    private bool $estPublie = false;
    private int $idUtilisateur = 0;

    // Propriété pour gérer la relation Many-to-Many
    private array $categoriesIds = [];

    // Propriété spéciale pour stocker les erreurs
    private array $erreurs = [];

    /**
     * Le constructeur valide tout, mais ne plante pas.
     * Il stocke les problèmes dans $this->erreurs.
     */
    public function __construct(
        string $titre, 
        string $slug, 
        string $extrait,
        string $contenu, 
        int $idUtilisateur, 
        bool $estPublie = false,
        array $categoriesIds = [] 
    ) {
        // Validation ID Utilisateur
        if ($idUtilisateur <= 0) {
            $this->erreurs['id_utilisateur'] = "Utilisateur invalide (ID manquant ou incorrect).";
        }
        $this->idUtilisateur = $idUtilisateur;

        // Validation Titre
        $titreNettoye = trim($titre);
        if (empty($titreNettoye)) {
            $this->erreurs['titre'] = "Le titre est obligatoire.";
        } elseif (mb_strlen($titreNettoye) > 255) {
            $this->erreurs['titre'] = "Le titre est trop long (max 255 car.).";
        }
        $this->titre = $titreNettoye;

        // Validation Slug
        $slugNettoye = strtolower(trim($slug));
        if (empty($slugNettoye)) {
            $this->erreurs['slug'] = "Le slug est obligatoire.";
        } elseif (mb_strlen($slugNettoye) > 255) {
            $this->erreurs['slug'] = "Le slug est trop long.";
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slugNettoye)) {
            $this->erreurs['slug'] = "Format invalide (lettres minuscules, chiffres, tirets uniquement).";
        }
        $this->slug = $slugNettoye;

        // Validation Extrait (Spécifique Article)
        $extraitNettoye = trim($extrait);
        if (empty($extraitNettoye)) {
            $this->erreurs['extrait'] = "L'extrait est obligatoire pour le référencement.";
        }
        $this->extrait = $extraitNettoye;

        // Validation Contenu
        $contenuNettoye = trim($contenu);
        if (empty($contenuNettoye)) {
            $this->erreurs['contenu'] = "Le contenu ne peut pas être vide.";
        }
        $this->contenu = $contenuNettoye;

        // Autres champs
        $this->estPublie = $estPublie;
        $this->categoriesIds = array_map('intval', $categoriesIds);

        // Dates
        $this->datePublication = new DateTime();
        $this->dateModification = new DateTime();
    }

    public function getErreurs(): array
    {
        return $this->erreurs;
    }
    
    // Pour ajouter une erreur manuellement (ex: Slug doublon depuis le Repo)
    public function ajouterErreur(string $champ, string $message): void {
        $this->erreurs[$champ] = $message;
    }

    // --- Getters & Setters ---

    public function getIdArticle(): ?int
    {
        return $this->idArticle;
    }

    public function setIdArticle(int $idArticle): self
    {
        $this->idArticle = $idArticle;
        return $this;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $titreNettoye = trim($titre);
        if ($titreNettoye === '') {
            $this->erreurs['titre'] = "Le titre est obligatoire.";
        } elseif (mb_strlen($titreNettoye) > 255) {
            $this->erreurs['titre'] = "Le titre est trop long.";
        } else {
            $this->titre = $titreNettoye;
            unset($this->erreurs['titre']);
        }
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $slugNettoye = strtolower(trim($slug));
        if ($slugNettoye === '') {
            $this->erreurs['slug'] = "Le slug est obligatoire.";
        } elseif (mb_strlen($slugNettoye) > 255) {
            $this->erreurs['slug'] = "Le slug est trop long.";
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slugNettoye)) {
            $this->erreurs['slug'] = "Format invalide (lettres minuscules, chiffres, tirets uniquement).";
        } else {
            $this->slug = $slugNettoye;
            unset($this->erreurs['slug']);
        }
        return $this;
    }

    public function getExtrait(): string
    {
        return $this->extrait;
    }

    public function setExtrait(string $extrait): self
    {
        $extraitNettoye = trim($extrait);
        if ($extraitNettoye === '') {
            $this->erreurs['extrait'] = "L'extrait est obligatoire.";
        } elseif (mb_strlen($extraitNettoye) > 255) {
            $this->erreurs['extrait'] = "L'extrait est trop long.";
        } else {
            $this->extrait = $extraitNettoye;
            unset($this->erreurs['extrait']);
        }
        return $this;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $contenuNettoye = trim($contenu);
        if ($contenuNettoye === '') {
            $this->erreurs['contenu'] = "Le contenu ne peut pas être vide.";
        } else {
            $this->contenu = $contenuNettoye;
            unset($this->erreurs['contenu']);
        }
        return $this;
    }

    public function getDatePublication(): ?DateTime
    {
        return $this->datePublication;
    }

    public function setDatePublication(string|DateTime $date): self
    {
        $this->datePublication = is_string($date) ? new DateTime($date) : $date;
        return $this;
    }

    public function getDateModification(): ?DateTime
    {
        return $this->dateModification;
    }

    public function setDateModification(string|DateTime $date): self
    {
        $this->dateModification = is_string($date) ? new DateTime($date) : $date;
        return $this;
    }

    public function isEstPublie(): bool
    {
        return $this->estPublie;
    }

    public function setEstPublie(bool $estPublie): self
    {
        $this->estPublie = $estPublie;
        return $this;
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(int $idUtilisateur): self
    {
        if ($idUtilisateur <= 0) {
            $this->erreurs['id_utilisateur'] = "Utilisateur invalide.";
        } else {
            $this->idUtilisateur = $idUtilisateur;
            unset($this->erreurs['id_utilisateur']);
        }
        return $this;
    }

    public function getCategoriesIds(): array
    {
        return $this->categoriesIds;
    }

    public function setCategoriesIds(array $ids): self
    {
        $this->categoriesIds = array_map('intval', $ids);
        return $this;
    }
}