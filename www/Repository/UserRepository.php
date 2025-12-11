<?php

namespace App\Repository;

use PDO;
use App\Models\Users;
use App\Repository\Connexion;

class UserRepository
{
    private static ?UserRepository $instance = null;
    private PDO $db;

    private function __construct()
    {
        $this->db = Connexion::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Crée un nouvel utilisateur.
     * Utilise l'objet Users validé pour l'insertion.
     */
    public function create(Users $user): string
    {
        // 1. Définir le rôle (Admin si 1er user, sinon User)
        $roleId = $this->checkUserStatus();
        $user->setIdRole($roleId);

        // 2. Générer le token d'activation
        $activationToken = bin2hex(random_bytes(32));
        $user->setActivationToken($activationToken);

        // 3. Insertion SQL (PostgreSQL : public.users)
        $sql = "INSERT INTO public.users (id_role, email, name, password_hash, activation_token, is_active, created_at) 
                VALUES (:role, :email, :name, :pass, :token, 0, NOW())";

        $stmt = $this->db->prepare($sql);
        
        // On récupère les données validées depuis l'objet
        $stmt->execute([
            'role'  => $user->getIdRole(),
            'email' => $user->getEmail(),
            'name'  => $user->getName(),
            'pass'  => $user->getPasswordHash(), // Le hash est généré par le modèle Users
            'token' => $user->getActivationToken()
        ]);

        return $activationToken;
    }

    // --- LECTURE (Retourne des tableaux associatifs pour compatibilité) ---

    public function getUserByEmail(string $email)
    {
        $sql = "SELECT * FROM public.users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByName(string $name)
    {
        $sql = "SELECT * FROM public.users WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $id)
    {
        $sql = "SELECT * FROM public.users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM public.users ORDER BY id ASC";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ACTIONS ET MISES À JOUR ---

    public function confirmEmail(string $token): bool
    {
        $sql = "UPDATE public.users SET is_active = 1, activation_token = NULL WHERE activation_token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);

        return $stmt->rowCount() > 0;
    }

    public function requestPasswordReset(string $email): string
    {
        $resetToken = bin2hex(random_bytes(32));
        
        // Expiration dans 1 heure (syntaxe Postgres)
        $sql = "UPDATE public.users SET reset_token = ?, reset_expires = NOW() + INTERVAL '1 hour' WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$resetToken, $email]);

        return $resetToken;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        // 1. Vérifier le token
        $sql = "SELECT id FROM public.users WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // 2. Hasher le mot de passe (ici on hash manuellement car c'est un update partiel)
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sqlUpdate = "UPDATE public.users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute([$hashedPassword, $user['id']]);

        return $stmtUpdate->rowCount() > 0;
    }

    public function updateUser(int $id, int $roleId, int $isActive): bool
    {
        $sql = "UPDATE public.users SET id_role = ?, is_active = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $isActive, $id]);
        
        return $stmt->rowCount() > 0;
    }

    public function deleteUser(int $userId): bool
    {
        // Vérification des dépendances (pages publiées)
        if ($this->hasPublishedPage($userId)) {
            return false;
        }

        $sql = "DELETE FROM public.users WHERE id = ?";
        $statement = $this->db->prepare($sql);
        $statement->execute([$userId]);

        return $statement->rowCount() > 0;
    }

    // --- HELPERS ---

    private function checkUserStatus(): int 
    {
        $sql = "SELECT count(*) FROM public.users";
        $stmt = $this->db->query($sql);
        $count = (int) $stmt->fetchColumn(); 

        return ($count > 0) ? 2 : 1; // 1 = Admin, 2 = User
    }

    public function hasPublishedPage(int $userId): bool
    {
        // Vérifie la table 'page' (ou public.page)
        $sql = "SELECT COUNT(*) FROM public.page WHERE id_utilisateur = ?";
        $statement = $this->db->prepare($sql);
        $statement->execute([$userId]);

        return ((int) $statement->fetchColumn()) > 0;
    }
    
    // Debug method
    public function checkPassword()
    {
        $stmt = $this->db->query("SELECT id, name, email, password_hash FROM public.users WHERE name = 'ayaz'");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}