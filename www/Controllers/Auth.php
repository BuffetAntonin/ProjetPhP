<?php
namespace App\Controllers;

use App\Core\Email;
use App\Core\Render;
use App\Repository\UserRepository;

class Auth
{
    private UserRepository $userModel;
    private Email $emailService;

    public function __construct()
    {
        $this->userModel = UserRepository::getInstance();
        $this->emailService = new Email();
    }

    public function login(): void
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            error_log("Login attempt: email='$email', password='$password'");

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password_hash']) && $user['is_active']) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                header('Location: /dashboard');
                exit;
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        }

        $render = new Render("login", "backoffice");
        $render->assign("error", $error);
        $render->render();
    }

    public function register(): void
    {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $name = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Email format validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Le format de l'email est invalide";
            } elseif ($this->userModel->getUserByEmail($email)) {
                $error = "L'email existe déjà";
            } elseif (empty($name)) {
                $error = "Le nom d'utilisateur est obligatoire";
            } elseif ($this->userModel->getUserByName($name)) {
                $error = "Ce nom d'utilisateur est déjà utilisé";
            } elseif (empty($password)) {
                $error = "Le mot de passe est obligatoire";
            } elseif ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas";
            } elseif (strlen($password) < 8 ||
                      !preg_match('#[A-Z]#', $password) ||
                      !preg_match('#[a-z]#', $password) ||
                      !preg_match('#[0-9]#', $password) ||
                      !preg_match('/[!@#$%^&*()\-_+=\[\]{};:\'",.<>?\\/|`~]/', $password)) {

                $error = "Le mot de passe doit faire au moins 8 caractères avec une minuscule, une majuscule, un chiffre et un caractère spécial";
            } else {
                $activationToken = $this->userModel->register($email, $name, $password);
                
                // Si PHPMailer plante, commente la ligne suivante :
                $this->emailService->sendVerificationEmail($email, $name, $activationToken);
                
                $success = "Inscription réussie! Vérifiez votre email pour confirmer votre compte.";
            }
        }

        $render = new Render("register", "backoffice");
        $render->assign("error", $error);
        $render->assign("success", $success);
        $render->render();
    }

    public function passwordReset(): void
    {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';

            if (empty($email)) {
                $error = "Veuillez entrer votre email";
            } else {
                $user = $this->userModel->getUserByEmail($email);
                if ($user) {
                    $resetToken = $this->userModel->requestPasswordReset($email);
                    
                    // Si PHPMailer plante, commente la ligne suivante :
                    $this->emailService->sendPasswordResetEmail($email, $user['name'], $resetToken);
                    
                    $success = "Un email de réinitialisation a été envoyé.";
                } else {
                    $success = "Si cet email existe, un lien de réinitialisation a été envoyé.";
                }
            }
        }

        $render = new Render("password_reset", "backoffice");
        $render->assign("error", $error);
        $render->assign("success", $success);
        $render->render();
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function dashboard(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $render = new Render("dashboard", "backoffice");
        $render->assign("name", $_SESSION['name']);
        $render->assign("email", $_SESSION['email']);
        $render->render();
    }

    public function debugDb(): void
    {
        echo "<pre style='background:#f0f0f0;padding:20px;'>";
        echo "=== DATABASE DEBUG ===\n\n";

        $db = \App\Repository\Connexion::getInstance(); 

        try {
            $stmt = $db->query("SELECT id, email, name FROM users");
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo "All users in database:\n";
            print_r($users);
        } catch (\Exception $e) {
            echo "Erreur SQL : " . $e->getMessage();
        }
        
        echo "</pre>";
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        $error = null;
        $success = null;

        if (empty($token)) {
            $error = "Token de vérification manquant";
        } else {
            if ($this->userModel->confirmEmail($token)) {
                $success = "Email vérifié avec succès! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Token invalide ou expiré";
            }
        }

        $render = new Render("verify_email", "backoffice");
        $render->assign("error", $error);
        $render->assign("success", $success);
        $render->render();
    }

    public function resetPasswordForm(): void
    {
        $token = $_GET['token'] ?? '';
        $error = null;
        $success = null;

        if (empty($token)) {
            $error = "Token de réinitialisation manquant";
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($password) || empty($confirmPassword)) {
                $error = "Tous les champs sont obligatoires";
            } elseif ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas";
            } elseif (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères";
            } else {
                if ($this->userModel->resetPassword($token, $password)) {
                    $success = "Mot de passe réinitialisé avec succès!";
                } else {
                    $error = "Token invalide ou expiré";
                }
            }
        }

        $render = new Render("reset_password_form", "backoffice");
        $render->assign("error", $error);
        $render->assign("success", $success);
        $render->assign("token", $token);
        $render->render();
    }

    public function checkPassword(): void
    {
        // Utilisation de la méthode corrigée dans UserRepository
        $user = $this->userModel->checkPassword();
        
        echo "<pre style='background:#f0f0f0;padding:20px;font-family:monospace;'>";
        echo "=== PASSWORD DEBUG ===\n\n";
        
        if ($user) {
            print_r($user);
            // Petit test rapide si tu passes ?pwd=monmotdepasse dans l'URL
            $testPassword = $_GET['pwd'] ?? 'test123';
            echo "\nTesting password: '$testPassword'\n";
            echo "Verify result: " . (password_verify($testPassword, $user['password_hash']) ? 'TRUE' : 'FALSE') . "\n";
        } else {
            echo "User 'ayaz' not found for debug.";
        }
        echo "</pre>";
    }

    public function usersManagement(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Check if user is admin (id_role = 1)
        $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$currentUser || $currentUser['id_role'] != 1) {
            die("Accès refusé. Seuls les administrateurs peuvent gérer les utilisateurs.");
        }

        // Get all users
        $users = $this->userModel->getAllUsers();

        $error = null;
        $success = null;

        if (isset($_GET['error']) && $_GET['error'] === 'published_articles') {
            $error = "Impossible de supprimer cet utilisateur car il a des articles publiés.";
        }

        if (isset($_GET['success']) && $_GET['success'] === 'user_deleted') {
            $success = "Utilisateur supprimé avec succès!";
        }

        $render = new Render("users_management", "backoffice");
        $render->assign("users", $users);
        $render->assign("error", $error);
        $render->assign("success", $success);
        $render->render();
    }

    public function editUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Check if user is admin
        $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$currentUser || $currentUser['id_role'] != 1) {
            die("Accès refusé.");
        }

        $userId = $_GET['id'] ?? null;
        $error = null;
        $success = null;

        if (!$userId) {
            $error = "ID utilisateur manquant";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newRole = $_POST['role'] ?? null;
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                if ($newRole === null) {
                    $error = "Le rôle est obligatoire";
                } else {
                    $this->userModel->updateUser($userId, $newRole, $isActive);
                    $success = "Utilisateur mis à jour avec succès!";
                }
            }

            $user = $this->userModel->getUserById($userId);
        }

        $render = new Render("edit_user", "backoffice");
        $render->assign("user", $user ?? null);
        $render->assign("error", $error);
        $render->assign("success", $success);
        $render->render();
    }

    public function deleteUser(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Check if user is admin
        $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$currentUser || $currentUser['id_role'] != 1) {
            die("Accès refusé.");
        }

        $userId = $_GET['id'] ?? null;
        $error = null;
        $success = null;

        if (!$userId) {
            $error = "ID utilisateur manquant";
        } else {
            // Check if user has published articles
            if ($this->userModel->hasPublishedArticles($userId)) {
                $error = "Impossible de supprimer cet utilisateur car il a des articles publiés.";
            } else {
                $this->userModel->deleteUser($userId);
                $success = "Utilisateur supprimé avec succès!";
                header('Location: /users-management?success=user_deleted');
                exit;
            }
        }

        $render = new Render("delete_user", "backoffice");
        $render->assign("error", $error);
        $render->assign("success", $success);
        $render->render();
    }
}