<?php

class AuthController extends AbstractController
{

    public function __construct()
    {

        parent::__construct();
    }

    public function login(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $tokenManager = new CSRFTokenManager();
            $csrfToken = $tokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        $errorMessage = '';
        $successMessage = '';

        if ($error) {
            switch ($error) {
                case 'empty_fields':
                    $errorMessage = 'Veuillez remplir tous les champs.';
                    break;
                case 'invalid_email':
                    $errorMessage = 'L\'adresse email n\'est pas valide.';
                    break;
                case 'invalid_credentials':
                    $errorMessage = 'Email ou mot de passe incorrect.';
                    break;
                case 'invalid_user':
                    $errorMessage = 'Aucun compte trouvé avec cette adresse email.';
                    break;
                default:
                    $errorMessage = 'Une erreur est survenue lors de la connexion.';
            }
        }

        if ($success) {
            switch ($success) {
                case 'password_reset':
                    $successMessage = 'Votre mot de passe a été réinitialisé avec succès.';
                    break;
            }
        }

        $this->render("login", [
            'csrf_token' => $csrfToken,
            'error' => $errorMessage,
            'success' => $successMessage
        ]);
    }

    public function checkLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
            $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8') ?? '';
            $token = htmlspecialchars($_POST['csrf_token'], ENT_QUOTES, 'UTF-8') ?? '';

            $userManager = new UserManager();
            $user = $userManager->findByEmail($email);

            if ($user) {
                $tokenManager = new CSRFTokenManager();
                $validate = $tokenManager->validateCSRFToken($token);
                if (!$validate) {
                    die("Erreur CSRF.");
                }

                if (empty($email) || empty($password)) {
                    $this->redirect("index.php?route=login&error=empty_fields");
                    return;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->redirect("index.php?route=login&error=invalid_email");
                    return;
                }

                if (!password_verify($_POST['password'], $user->getPassword())) {
                    $this->redirect("index.php?route=login&error=invalid_credentials");
                    return;
                }

                $_SESSION['user'] = $user->getId();
                $_SESSION['username'] = $user->getUserName();
                $_SESSION['unique_id'] = $user->getUniqueId();

                $this->redirect("index.php?route=home");
            } else {
                $this->redirect("index.php?route=login&error=invalid_user");
            }
        } else {
            $this->redirect("index.php?route=login");
        }
    }

    public function register(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $tokenManager = new CSRFTokenManager();
            $csrfToken = $tokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        $error = $_GET['error'] ?? null;
        $errorMessage = '';

        if ($error) {
            switch ($error) {
                case 'empty_fields':
                    $errorMessage = 'Tous les champs sont obligatoires.';
                    break;
                case 'invalid_email':
                    $errorMessage = 'L\'adresse email n\'est pas valide.';
                    break;
                case 'password_mismatch':
                    $errorMessage = 'Les mots de passe ne correspondent pas.';
                    break;
                case 'weak_password':
                    $errorMessage = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
                    break;
                case 'email_exists':
                    $errorMessage = 'Cette adresse email est déjà utilisée.';
                    break;
                case 'creation_failed':
                    $errorMessage = 'Erreur lors de la création du compte. Veuillez réessayer.';
                    break;
                default:
                    $errorMessage = 'Une erreur est survenue.';
            }
        }

        $this->render("register", [
            'csrf_token' => $csrfToken,
            'error' => $errorMessage
        ]);
    }


    public function checkRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') ?? '';
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
            $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8') ?? '';
            $confirmPassword = htmlspecialchars($_POST['confirm-password'], ENT_QUOTES, 'UTF-8') ?? '';
            $token = htmlspecialchars($_POST['csrf_token'], ENT_QUOTES, 'UTF-8') ?? '';

            $tokenManager = new CSRFTokenManager();
            $validate = $tokenManager->validateCSRFToken($token);
            if (!$validate) {
                die("Erreur CSRF.");
            }

            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                $this->redirect("index.php?route=register&error=empty_fields");
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->redirect("index.php?route=register&error=invalid_email");
                return;
            }

            if ($_POST['password'] !== $_POST['confirm-password']) {
                $this->redirect("index.php?route=register&error=password_mismatch");
                return;
            }

            $passwordManager = new PasswordManager();
            $verifyedPassword = $passwordManager->validatePassword($_POST['password']);
            if (!$verifyedPassword) {
                $this->redirect("index.php?route=register&error=weak_password");
                return;
            }

            $userManager = new UserManager();
            $verifyedEmail = $userManager->findByEmail($email);
            if ($verifyedEmail) {
                $this->redirect("index.php?route=register&error=email_exists");
                return;
            }

            $user = new User();
            $user->setUserName($username);
            $user->setEmail($email);
            $user->setPassword($password);

            $createUser = $userManager->create($user);

            if ($createUser) {
                $_SESSION['user'] = $user->getId();
                $_SESSION['username'] = $user->getUserName();
                $_SESSION['unique_id'] = $user->getUniqueId();

                $this->redirect("index.php?route=login");
            } else {
                $this->redirect("index.php?route=register&error=creation_failed");
            }
        } else {
            $this->redirect("index.php?route=register");
        }
    }

    public function resetPassword(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $tokenManager = new CSRFTokenManager();
            $csrfToken = $tokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        $this->render("reset-password", ['csrf_token' => $csrfToken]);
    }

    public function checkResetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
            $newPassword = htmlspecialchars($_POST['new-password'], ENT_QUOTES, 'UTF-8') ?? '';
            $confirmPassword = htmlspecialchars($_POST['confirm-password'], ENT_QUOTES, 'UTF-8') ?? '';
            $token = htmlspecialchars($_POST['csrf_token'], ENT_QUOTES, 'UTF-8') ?? '';

            $userManager = new UserManager();
            $user = $userManager->findByEmail($email);

            $tokenManager = new CSRFTokenManager();
            $validate = $tokenManager->validateCSRFToken($token);
            if (!$validate) {
                die("Erreur CSRF.");
            }

            if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
                $this->redirect("index.php?route=reset-password&error=empty_fields");
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->redirect("index.php?route=reset-password&error=invalid_email");
                return;
            }

            if (!$user) {
                $this->redirect("index.php?route=reset-password&error=user_not_found");
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $this->redirect("index.php?route=reset-password&error=password_mismatch");
                return;
            }

            $passwordManager = new PasswordManager();
            $verifyedPassword = $passwordManager->validatePassword($newPassword);
            if (!$verifyedPassword) {
                $this->redirect("index.php?route=reset-password&error=weak_password");
                return;
            }

            $user->setPassword($newPassword);
            $userManager = new UserManager();
            $updateSuccess = $userManager->updatePassword($user);

            if ($updateSuccess) {
                $this->redirect("index.php?route=login&success=password_reset");
            } else {
                $this->redirect("index.php?route=reset-password&error=update_failed");
            }
        } else {
            $this->redirect("index.php?route=reset-password");
        }
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect("index.php?route=login");
    }
}
