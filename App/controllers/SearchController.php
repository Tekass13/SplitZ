<?php
class SearchController extends AbstractController
{
    private SearchManager $sm;
    private UserManager $um;
    private CSRFTokenManager $csrfm;
    public function __construct()
    {
        $this->sm = new SearchManager();
        $this->um = new UserManager();
        $this->csrfm = new CSRFTokenManager();

        if (!isset($_SESSION['user'])) {
            return;
        }
    }

    public function searchUser(): void
    {

        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $this->csrfm->generateCSRFToken();
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
                $users = $this->sm->searchUsers($searchTerm, $userId);
            } else {
                $users = $this->um->getAllUsers();
            }
        }

        $this->render("search-result", [
            'users' => $users,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function searchContact(): void
    {

        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $this->csrfm->generateCSRFToken();
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
                $users = $this->sm->searchContacts($searchTerm, $userId);
            } else {
                $users = $this->sm->getAllContacts($userId);
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
