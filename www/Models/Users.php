<?php 

namespace App\Models;

use DateTime;

class Users
{
    private ?int $id = null;
    private string $email = '';
    private string $name = '';
    private string $passwordHash = ''; // On stocke le hash, pas le mot de passe clair
    private int $idRole = 2; // 2 = User par défaut
    private bool $isActive = false;
    private ?string $activationToken = null;
    private ?string $resetToken = null;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    private array $errors = [];

    /**
     * Constructeur "Strict" : Valide les données d'inscription immédiatement.
     * Prend le mot de passe en CLAIR pour vérifier sa complexité.
     */
    public function __construct(
        string $email, 
        string $name, 
        string $password, 
        string $confirmPassword
    ) {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();

        // 1. Validation Email
        $this->setEmail($email);

        // 2. Validation Nom
        $this->setName($name);

        // 3. Validation Mot de passe (Complexité + Correspondance)
        $this->validateAndSetPassword($password, $confirmPassword);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    // --- Logique métier interne ---

    /**
     * Valide la complexité du mot de passe et génère le hash si tout est bon.
     */
    private function validateAndSetPassword(string $password, string $confirmPassword): void
    {
        if (empty($password)) {
            $this->errors['password'] = "Le mot de passe est obligatoire";
        } elseif ($password !== $confirmPassword) {
            $this->errors['password'] = "Les mots de passe ne correspondent pas";
        } elseif (strlen($password) < 8 ||
                  !preg_match('#[A-Z]#', $password) ||
                  !preg_match('#[a-z]#', $password) ||
                  !preg_match('#[0-9]#', $password) ||
                  !preg_match('/[!@#$%^&*()\-_+=\[\]{};:\'",.<>?\\/|`~]/', $password)) {
            
            $this->errors['password'] = "Le mot de passe doit faire au moins 8 caractères avec une minuscule, une majuscule, un chiffre et un caractère spécial";
        } else {
            // Si aucune erreur, on hash et on stocke
            $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $cleaned = trim($email);
        if (empty($cleaned)) {
            $this->errors['email'] = "L'email est obligatoire";
        } elseif (!filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = "Le format de l'email est invalide";
        } else {
            $this->email = $cleaned;
            // On retire l'erreur si elle a été corrigée lors d'un update
            unset($this->errors['email']);
        }
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $cleaned = trim($name);
        if (empty($cleaned)) {
            $this->errors['name'] = "Le nom d'utilisateur est obligatoire";
        } elseif (mb_strlen($cleaned) > 255) {
            $this->errors['name'] = "Le nom est trop long";
        } else {
            $this->name = $cleaned;
            unset($this->errors['name']);
        }
        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    // Utilisé si on charge depuis la BDD directement (sans revalider le mdp clair)
    public function setPasswordHash(string $hash): self
    {
        $this->passwordHash = $hash;
        return $this;
    }

    public function getIdRole(): int
    {
        return $this->idRole;
    }

    public function setIdRole(int $role): self
    {
        $this->idRole = $role;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $active): self
    {
        $this->isActive = $active;
        return $this;
    }
    
    public function getActivationToken(): ?string { return $this->activationToken; }
    public function setActivationToken(?string $t): self { $this->activationToken = $t; return $this; }
    
    public function getResetToken(): ?string { return $this->resetToken; }
    public function setResetToken(?string $t): self { $this->resetToken = $t; return $this; }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function setCreatedAt($d): self { $this->createdAt = is_string($d) ? new DateTime($d) : $d; return $this; }
    
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }
    public function setUpdatedAt($d): self { $this->updatedAt = is_string($d) ? new DateTime($d) : $d; return $this; }
}