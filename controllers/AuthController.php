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

        $this->render("login", ['csrf_token' => $csrfToken]);
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

        $this->render("register", ['csrf_token' => $csrfToken]);
    }


    public function checkRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') ?? '';
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
            $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8') ?? '';
            $confirmPassword = htmlspecialchars($_POST['confirm-password'], ENT_QUOTES, 'UTF-8') ?? '';
            $token = htmlspecialchars($_POST['csrf_token'], ENT_QUOTES, 'UTF-8') ?? '';

            $user = new User();
            $user->setUserName($username);
            $user->setEmail($email);
            $user->setPassword($password);

            $userManager = new UserManager();
            $createUser = $userManager->create($user);

            $tokenManager = new CSRFTokenManager();
            $validate = $tokenManager->validateCSRFToken($token);

            $passwordManager = new PasswordManager();
            $verifyedPassword = $passwordManager->validatePassword($_POST['password']);
            $verifyedEmail = $userManager->findByEmail($email);


            if (!$validate) {
                die("Erreur CSRF.");
            }

            if (empty($email) || empty($password) || empty($confirmPassword)) {
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

            if (!$verifyedPassword) {
                $this->redirect("index.php?route=register&error=weak_password");
                return;
            }

            if ($verifyedEmail === $email) {
                $this->redirect("index.php?route=register&error=email_exists");
                return;
            }

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
