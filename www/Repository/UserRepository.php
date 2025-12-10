<?php
namespace App\Repository;

use PDO;

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

    public function register($email, $name, $password)
    {
        // 1. On appelle la fonction pour définir le rôle
        // Si la base est vide, $roleId sera 1 (Admin). Sinon, ce sera 2 (Utilisateur standard).
        $roleId = $this->checkUserStatus();

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $activationToken = bin2hex(random_bytes(32));

        $sql = "INSERT INTO users (id_role, email, name, password_hash, activation_token, is_active) VALUES (?, ?, ?, ?, ?, 0)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $email, $name, $hashedPassword, $activationToken]);

        return $activationToken;
    }
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetch();
    }

    public function getUserByName($name)
    {
        $sql = "SELECT * FROM users WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);

        return $stmt->fetch();
    }

    public function confirmEmail($token)
    {
        $sql = "UPDATE users SET is_active = 1 WHERE activation_token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);

        return $stmt->rowCount() > 0;
    }

    public function requestPasswordReset($email)
    {
        $resetToken = bin2hex(random_bytes(32));
        
        $sql = "UPDATE users SET reset_token = ?, reset_expires = NOW() + INTERVAL '1 hour' WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$resetToken, $email]);

        return $resetToken;
    }

    public function resetPassword($token, $newPassword)
    {
        // 1. Vérifier le token
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // 2. Mettre à jour le mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $sqlUpdate = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute([$hashedPassword, $user['id']]);

        return $stmtUpdate->rowCount() > 0;
    }

    public function authenticate($name, $password)
    {
        $user = $this->getUserByName($name);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        if (!$user['is_active']) {
            return null; 
        }

        return $user;
    }
    
    public function checkPassword()
    {
        // On utilise $this->db et la syntaxe PDO standard
        $sql = "SELECT id, name, email, password_hash, reset_token, reset_expires, is_active FROM users WHERE name = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ayaz']);
        
        $user = $stmt->fetch(); 

        return $user;
    }

<<<<<<< HEAD
    private function checkUserStatus(): int 
=======
private function checkUserStatus(): int 
>>>>>>> 12515dcc3e67585c9fde818c79c2626792090f7b
    {
        $sql = "SELECT count(*) FROM users";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $count = (int) $stmt->fetchColumn(); 

        return ($count > 0) ? 2 : 1;
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 12515dcc3e67585c9fde818c79c2626792090f7b
