<?php

namespace App\Models;

use DateTime;

class Page
{
    // DB Properties
    private ?int $id = null; // "idPage" devient "id" (standard)
    private string $title = '';
    private string $slug = '';
    private string $content = '';
    private int $userId = 0;
    private bool $isPublished = false;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    // Special property for errors
    private array $errors = [];

    /**
     * The constructor validates everything but doesn't crash.
     * It stores issues in $this->errors.
     */
    public function __construct(
        string $title, 
        string $slug, 
        string $content, 
        int $userId,
        bool $isPublished = false
    ) {
        if ($userId <= 0) {
            $this->errors['user_id'] = "Utilisateur invalide (ID manquant ou incorrect).";
        }
        $this->userId = $userId;

        $cleanedTitle = trim($title);
        if (empty($cleanedTitle)) {
            $this->errors['title'] = "Le titre est obligatoire.";
        } elseif (mb_strlen($cleanedTitle) > 255) {
            $this->errors['title'] = "Le titre est trop long (max 255 car.).";
        }
        else { // je l'ai supprime dans mon code orginale quand j'ai urlEncode
            $routes = yaml_parse_file("../routes.yml"); 
            $routesStatiques = array_keys($routes);

            if (in_array('/' . $slugNettoye, $routesStatiques)) {
                $this->erreurs['slug'] = "Ce slug est réservé";
            }
        }
        $this->title = $cleanedTitle;

        

        // 1. Nettoyage de base
        $cleanedSlug = strtolower(trim($slug));

        // 3. Validation (Longueur et présence uniquement)
        if (empty($cleanedSlug)) {
            $this->errors['slug'] = "Le slug est obligatoire.";
        } elseif (strlen($cleanedSlug) > 255) {
            // Attention : urlencode rallonge la chaîne (ex: 1 caractère accentué devient 6 caractères)
            $this->errors['slug'] = "Le slug est trop long.";
        }

        // 4. Assignation (S'il n'y a pas d'erreur, on enregistre)
        if (empty($this->errors['slug'])) {
            $this->slug = $cleanedSlug;
        }

        $cleanedContent = trim($content);
        if (empty($cleanedContent)) {
            $this->errors['content'] = "Le contenu ne peut pas être vide.";
        }
        $this->content = $cleanedContent;

        $this->isPublished = $isPublished;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $cleanedTitle = trim($title);

        if ($cleanedTitle === '') {
            $this->errors['title'] = "Le titre est obligatoire.";
        } elseif (mb_strlen($cleanedTitle) > 255) {
            $this->errors['title'] = "Le titre est trop long (max 255 car.).";
        } else {
            $this->title = $cleanedTitle;
            unset($this->errors['title']); // Remove error if fixed
        }

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $cleanedSlug = strtolower(trim($slug));

        if ($cleanedSlug === '') {
            $this->errors['slug'] = "Le slug est obligatoire.";
        } elseif (mb_strlen($cleanedSlug) > 255) {
            $this->errors['slug'] = "Le slug est trop long.";
        } elseif (!preg_match('/^[a-z0-9-]+$/', $cleanedSlug)) {
            $this->errors['slug'] = "Le slug contient des caractères interdits (lettres, chiffres, tirets uniquement).";
        } else {
            $this->slug = $cleanedSlug;
            unset($this->errors['slug']);
        }

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $cleanedContent = trim($content);

        if ($cleanedContent === '') {
            $this->errors['content'] = "Le contenu ne peut pas être vide.";
        } else {
            $this->content = $cleanedContent;
            unset($this->errors['content']);
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    // Accepts string (from DB) or DateTime object
    public function setCreatedAt($date): self
    {
        // Adaptation du typage pour correspondre à la logique originale
        $this->createdAt = is_string($date) ? new DateTime($date) : $date;
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($date): self
    {
        $this->updatedAt = is_string($date) ? new DateTime($date) : $date;
        return $this;
    }

    // Standard naming convention for booleans is "is..." or "has..."
    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        if ($userId <= 0) {
            $this->errors['user_id'] = "Utilisateur invalide (ID manquant ou incorrect).";
        } else {
            $this->userId = $userId;
            unset($this->errors['user_id']);
        }

        return $this;
    }
}