<?php

class AuthController extends AbstractController
{
    private CSRFTokenManager $csrfm;
    private PasswordManager $pm;
    private UserManager $um;

    public function __construct()
    {
        $this->csrfm = new CSRFTokenManager();
        $this->pm = new PasswordManager();
        $this->um = new UserManager();

        parent::__construct();
    }

    public function login(): void
    {
        if (empty($_SESSION['csrf_token'])) {

            $csrfToken = $this->csrfm->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {

            $csrfToken = $_SESSION['csrf_token'];
        }

        $this->render("login", ['csrf_token' => $csrfToken]);
    }

    public function checkLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $token = $_POST['csrf_token'] ?? '';

            $user = $this->um->findByEmail($email);

            if ($user) {

                $validate = $this->csrfm->validateCSRFToken($token);
                if (!$validate) {
                    // var_dump($token);
                    die("Erreur CSRF.");
                }

                if (empty($email) || empty($password)) {
                    var_dump($email, $password);
                    $this->redirect("index.php?route=login&error=empty_fields");
                    return;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    var_dump($email);
                    $this->redirect("index.php?route=login&error=invalid_email");
                    return;
                }

                if (!password_verify($_POST['password'], $user->getPassword())) {
                    var_dump($password, $user->getPassword());
                    $this->redirect("index.php?route=login&error=invalid_credentials");
                    return;
                }

                $_SESSION['user'] = $user->getId();
                $_SESSION['username'] = $user->getUserName();

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

            $csrfToken = $this->csrfm->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {

            $csrfToken = $_SESSION['csrf_token'];
        }

        $this->render("register", ['csrf_token' => $csrfToken]);
    }


    public function checkRegister(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $username = htmlspecialchars($_POST['username']) ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm-password'] ?? '';
            $token = $_POST['csrf_token'] ?? '';

            $user = new User();
            $user->setUserName($username);
            $user->setEmail($email);
            $user->setPassword($password);
            $createUser = $this->um->create($user);

            $validate = $this->csrfm->validateCSRFToken($token);
            $verifyedPassword = $this->pm->validatePassword($_POST['password']);
            $verifyedEmail = $this->um->findByEmail($email);

            if (!$validate) {
                die("Erreur CSRF.");
            }

            if (empty($email) || empty($password) || empty($confirmPassword)) {
                var_dump($email, $password, $confirmPassword);
                $this->redirect("index.php?route=register&error=empty_fields");
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                var_dump($email);
                $this->redirect("index.php?route=register&error=invalid_email");
                return;
            }

            if ($_POST['password'] !== $_POST['confirm-password']) {
                var_dump($_POST['password'], $_POST['confirm-password']);
                $this->redirect("index.php?route=register&error=password_mismatch");
                return;
            }

            if (!$verifyedPassword) {
                var_dump($_POST['password']);
                $this->redirect("index.php?route=register&error=weak_password");
                return;
            }

            if ($verifyedEmail === $email) {
                var_dump($verifyedEmail, $email);
                $this->redirect("index.php?route=register&error=email_exists");
                return;
            }

            if ($createUser) {

                $_SESSION['user'] = $user->getId();
                $_SESSION['username'] = $user->getUserName();

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
            $csrfToken = $this->csrfm->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        $this->render("reset-password", ['csrf_token' => $csrfToken]);
    }

    public function checkResetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'] ?? '';
            $newPassword = $_POST['new-password'] ?? '';
            $confirmPassword = $_POST['confirm-password'] ?? '';
            $token = $_POST['csrf_token'] ?? '';

            $user = $this->um->findByEmail($email);

            $validate = $this->csrfm->validateCSRFToken($token);
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

            $verifyedPassword = $this->pm->validatePassword($newPassword);
            if (!$verifyedPassword) {
                $this->redirect("index.php?route=reset-password&error=weak_password");
                return;
            }

            $user->setPassword($newPassword);
            $updateSuccess = $this->um->updatePassword($user);

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
