<?php
namespace App\Controllers;

use App\Core\Email;
use App\Core\Render;
use App\Models\Users; // Importation du Modèle Users
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

            error_log("Login attempt: email='$email'");

            // On récupère l'utilisateur (tableau associatif)
            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password_hash']) && $user['is_active']) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                // Ajout possible du rôle en session si besoin
                // $_SESSION['role'] = $user['id_role'];

                header('Location: /dashboard');
                exit;
            } else {
                $error = "Email ou mot de passe incorrect, ou compte non activé.";
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
            
            // 1. Instanciation du modèle Users.
            // Le constructeur va valider le format Email, Nom et la complexité du Mot de passe.
            $user = new Users(
                $_POST['email'] ?? '',
                $_POST['username'] ?? '',
                $_POST['password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );

            // 2. On récupère les erreurs de validation du modèle
            $modelErrors = $user->getErrors();

            if (!empty($modelErrors)) {
                // S'il y a des erreurs de format, on affiche la première
                $error = reset($modelErrors);
            } 
            // 3. Si le format est OK, on vérifie l'unicité en base de données
            elseif ($this->userModel->getUserByEmail($user->getEmail())) {
                $error = "L'email existe déjà";
            } 
            elseif ($this->userModel->getUserByName($user->getName())) {
                $error = "Ce nom d'utilisateur est déjà utilisé";
            } 
            else {
                // 4. Tout est bon, on insère via le Repository
                // On passe l'objet $user complet
                $activationToken = $this->userModel->create($user);
                
                // Envoi de l'email
                $this->emailService->sendVerificationEmail($user->getEmail(), $user->getName(), $activationToken);
                
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
                    $this->emailService->sendPasswordResetEmail($email, $user['name'], $resetToken);
                    $success = "Un email de réinitialisation a été envoyé.";
                } else {
                    // Message vague pour la sécurité
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
            } elseif (strlen($password) < 8) { // Validation basique ici, car c'est un reset
                $error = "Le mot de passe doit contenir au moins 8 caractères";
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
        $user = $this->userModel->checkPassword();
        
        echo "<pre style='background:#f0f0f0;padding:20px;font-family:monospace;'>";
        echo "=== PASSWORD DEBUG ===\n\n";
        
        if ($user) {
            print_r($user);
            $testPassword = $_GET['pwd'] ?? 'test123';
            echo "\nTesting password: '$testPassword'\n";
            echo "Verify result: " . (password_verify($testPassword, $user['password_hash']) ? 'TRUE' : 'FALSE') . "\n";
        } else {
            echo "User 'ayaz' not found for debug.";
        }
        echo "</pre>";
    }

    // --- GESTION DES UTILISATEURS (ADMIN) ---

    public function usersManagement(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Vérification Admin
        $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$currentUser || $currentUser['id_role'] != 1) {
            die("Accès refusé. Seuls les administrateurs peuvent gérer les utilisateurs.");
        }

        $users = $this->userModel->getAllUsers();
        $error = null;
        $success = null;

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

        // Vérification Admin
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
            $userTarget = $this->userModel->getUserById($userId);

            if (!$userTarget) {
                $error = "Utilisateur introuvable";
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $newRole = $_POST['role'] ?? null;
                    $isActive = isset($_POST['is_active']) ? 1 : 0;

                    if ($newRole === null) {
                        $error = "Le rôle est obligatoire";
                    } else {
                        // Empêcher l'auto-rétrogradation de l'admin courant
                        if ((int)$userId === (int)$_SESSION['user_id'] && (int)$newRole !== (int)$userTarget['id_role']) {
                            $error = "Vous ne pouvez pas modifier votre propre rôle.";
                        } else {
                            $this->userModel->updateUser($userId, $newRole, $isActive);
                            $success = "Utilisateur mis à jour avec succès!";
                            // Recharger les données pour l'affichage
                            $userTarget = $this->userModel->getUserById($userId);
                        }
                    }
                }
            }
        }

        $render = new Render("edit_user", "backoffice");
        $render->assign("user", $userTarget ?? null);
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

        // Vérification Admin
        $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$currentUser || $currentUser['id_role'] != 1) {
            die("Accès refusé.");
        }

        $userId = $_GET['id'] ?? null;
        $errorMessage = null;
        // On n'a pas besoin de message de succès ici car on redirige si ça marche, 
        // mais on doit quand même passer "null" à la vue pour éviter l'erreur "Undefined variable".

        if (!$userId) {
            $errorMessage = "ID utilisateur manquant";
        } else {
            if ((int)$userId === (int)$_SESSION['user_id']) {
                 $error = "Vous ne pouvez pas modifier votre propre rôle.";
                 $isDeleted = null;
            } else {
                $isDeleted = $this->userModel->deleteUser($userId);
            }
            

            if ($isDeleted) {
                // SUCCÈS : On redirige, donc pas besoin d'afficher la vue
                header('Location: /users-management?success=user_deleted');
                exit;
            } else {
                // ÉCHEC
                $errorMessage = "Impossible de supprimer cet utilisateur";
            }
        }

        $renderer = new Render("delete_user", "backoffice");
        $renderer->assign("error", $errorMessage);
        $renderer->assign("success", null); // <--- LIGNE AJOUTÉE POUR CORRIGER LE WARNING
        $renderer->render();
    }
}