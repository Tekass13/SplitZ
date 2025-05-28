<?php
class SearchController extends AbstractController
{
    public function __construct() {}

    public function searchUser(): void
    {
        $message = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        if (empty($_SESSION['csrf_token'])) {
            $tokenManager = new CSRFTokenManager();
            $csrfToken = $tokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        $users = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search-bar'])) {
            $searchTerm = htmlspecialchars($_POST['search-bar']);
            $searchCategory = $_POST['search-category'] ?? 'all';

            $userId = $_SESSION['user'];

            if (!empty($searchCategory)) {
                $searchManager = new SearchManager();
                $users = $searchManager->searchUsers($searchTerm, $userId);
            } else {
                $searchManager = new SearchManager();
                $users = $searchManager->getAllUsers($userId);
            }
        }

        $this->render("search-result", [
            'users' => $users,
            'csrf_token' => $_SESSION['csrf_token'],
            'message' => $message
        ]);
    }

    public function searchContact(): void
    {

        if (empty($_SESSION['csrf_token'])) {
            $tokenManager = new CSRFTokenManager();
            $csrfToken = $tokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        $users = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search-bar'])) {
            $searchTerm = htmlspecialchars($_POST['search-bar']);
            $searchCategory = $_POST['search-category'] ?? 'all';

            $userId = $_SESSION['user'];

            if (!empty($searchCategory)) {
                $searchManager = new SearchManager();
                $users = $searchManager->searchContacts($searchTerm, $userId);
            } else {
                $searchManager = new SearchManager();
                $users = $searchManager->getAllContacts($userId);
            }
        }

        $this->render("search-result", [
            'users' => $users,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function searchTransaction(string $search): void
    {
        $this->render('search-result', []);
    }
}
