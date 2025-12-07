<?php
namespace App\Accesseur;

use App\Modele\Utilisateur;
use PDO;
require_once __DIR__ . '/configuration.php';

class AccesseurConnexion
{
    // Insert a new user. Returns empty string on success, or error message.
    public function inscription(Utilisateur $u)
    {
        try {
            $pdo = getPDO();
            // check existing email
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$u->email]);
            if ($stmt->fetch()) {
                return 'Un compte existe déjà pour cet e-mail.';
            }

            $passwordHash = password_hash($u->password, PASSWORD_DEFAULT);
            $token = $u->activation_token;
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, activation_token, is_active, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
            $stmt->execute([$u->name, $u->email, $passwordHash, $token]);
            return '';
        } catch (\Exception $e) {
            return 'Erreur serveur: ' . $e->getMessage();
        }
    }

    public function activate($token)
    {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare('UPDATE users SET is_active = 1, activation_token = NULL WHERE activation_token = ?');
            $stmt->execute([$token]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function login($email, $password)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, password_hash, is_active, name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user)
            return [false, 'Utilisateur introuvable.'];
        if (!$user['is_active'])
            return [false, 'Compte non activé. Vérifiez votre e-mail.'];
        if (!password_verify($password, $user['password_hash']))
            return [false, 'Mot de passe incorrect.'];
        return [true, $user];
    }

    public function createResetToken($email)
    {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user)
                return false;
            $token = bin2hex(random_bytes(16));
            $lifetime = defined('RESET_TOKEN_LIFETIME') ? (int) RESET_TOKEN_LIFETIME : 3600;
            $expires = date('Y-m-d H:i:s', time() + $lifetime);
            $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?');
            $stmt->execute([$token, $expires, $email]);
            return $token;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function verifyResetToken($token)
    {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE reset_token = ? AND reset_expires > NOW()');
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function resetPassword($token, $newPassword)
    {
        try {
            $pdo = getPDO();
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?');
            $stmt->execute([$hash, $token]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}

?>