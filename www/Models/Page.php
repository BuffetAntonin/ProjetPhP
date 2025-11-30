<?php

namespace App\Models;

use DateTime;



class Page
{
    // Propriétés de la BDD
    private ?int $idPage = null;
    private string $titre = '';
    private string $slug = '';
    private string $contenu = '';
    private int $idUtilisateur = 0;
    private bool $estPublie = false;
    private DateTime $dateCreation;
    private DateTime $dateModification;

    // Propriété spéciale pour stocker les erreurs
    private array $erreurs = [];

    /**
     * Le constructeur valide tout, mais ne plante pas.
     * Il stocke les problèmes dans $this->erreurs.
     */
    public function __construct(
        string $titre, 
        string $slug, 
        string $contenu, 
        int $idUtilisateur,
        bool $estPublie = false
    ) {
        if ($idUtilisateur <= 0) {
            $this->erreurs['id_utilisateur'] = "Utilisateur invalide (ID manquant ou incorrect).";
        }
        $this->idUtilisateur = $idUtilisateur;

        $titreNettoye = trim($titre);
        if (empty($titreNettoye)) {
            $this->erreurs['titre'] = "Le titre est obligatoire.";
        } elseif (mb_strlen($titreNettoye) > 255) {
            $this->erreurs['titre'] = "Le titre est trop long (max 255 car.).";
        }
        $this->titre = $titreNettoye;

        $slugNettoye = strtolower(trim($slug));
        if (empty($slugNettoye)) {
            $this->erreurs['slug'] = "Le slug est obligatoire.";
        } elseif (mb_strlen($slugNettoye) > 255) {
            $this->erreurs['slug'] = "Le slug est trop long.";
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slugNettoye)) {
            $this->erreurs['slug'] = "Le slug contient des caractères interdits (seulement lettres, chiffres, tirets).";
        }
        $this->slug = $slugNettoye;

        $contenuNettoye = trim($contenu);
        if (empty($contenuNettoye)) {
            $this->erreurs['contenu'] = "Le contenu ne peut pas être vide.";
        }
        $this->contenu = $contenuNettoye;

        $this->estPublie = $estPublie;
        $this->dateCreation = new DateTime();
        $this->dateModification = new DateTime();
    }

    public function getErreurs(): array
    {
        return $this->erreurs;
    }

    // --- Getters & Setters ---

    public function getIdPage(): ?int
    {
        return $this->idPage;
    }

    public function setIdPage(int $idPage): self
    {
        $this->idPage = $idPage;
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
            $this->erreurs['titre'] = "Le titre est trop long (max 255 car.).";
        } else {
            $this->titre = $titreNettoye;
            unset($this->erreurs['titre']); // supprime l'erreur si corrigée
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
            $this->erreurs['slug'] = "Le slug contient des caractères interdits (lettres, chiffres, tirets uniquement).";
        } else {
            $this->slug = $slugNettoye;
            unset($this->erreurs['slug']);
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


    public function getDateCreation(): ?DateTime
    {
        return $this->dateCreation;
    }

    // Accepte une chaîne (venant de la DB) ou un objet DateTime
    public function setDateCreation(string|DateTime $date): self
    {
        $this->dateCreation = is_string($date) ? new DateTime($date) : $date;
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
            $this->erreurs['id_utilisateur'] = "Utilisateur invalide (ID manquant ou incorrect).";
        } else {
            $this->idUtilisateur = $idUtilisateur;
            unset($this->erreurs['id_utilisateur']);
        }

        return $this;
    }

}