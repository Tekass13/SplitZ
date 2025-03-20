<?php

class ContactController extends AbstractController
{
    private ContactManager $cm;
    private UserManager $um;
    private CSRFTokenManager $csrfm;

    public function __construct()
    {
        $this->cm = new ContactManager();
        $this->um = new UserManager();
        $this->csrfm = new CSRFTokenManager();

        if (!isset($_SESSION['user'])) {
            return;
        }
    }

    public function addContact(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user'];
            $identifier = filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$userId || !$identifier) {
                echo "Données invalides.";
                return;
            }

            if ($this->cm->insertContact($userId, $identifier)) {
                header('Location: index.php?route=list-contact&status=success');
                exit;
            } else {
                echo "Utilisateur introuvable ou erreur lors de l'ajout.";
            }
        } else {
            $this->showContacts();
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

            switch ($searchCategory) {
                case 'username':
                    $users = $this->um->searchUsersByUsername($searchTerm, $userId);
                    break;

                case 'email':
                    $users = $this->um->searchUsersByEmail($searchTerm, $userId);
                    break;

                case 'all':
                default:
                    $users = $this->um->searchUsers($searchTerm, $userId);
                    break;
            }
        }

        $this->render("search-result", [
            'users' => $users,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function showContacts(): void
    {
        $userId = $_SESSION['user'];
        $contacts = $this->cm->getAllContacts($userId);

        $this->render("list-contact", ["contacts" => $contacts]);
    }

    public function deleteContact(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['id'])) {
            $this->redirect("index.php?route=list-contact&error=invalid_request");
            return;
        }

        $contactId = $_GET['id'];
        $userId = $_SESSION['user'];

        if ($contactId <= 0) {
            $this->redirect("index.php?route=list-contact&error=invalid_contact");
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->csrfm->validateCSRFToken($token)) {
            die("Erreur CSRF.");
        }

        $success = $this->cm->deleteContact($userId, $contactId);

        if ($success) {
            $this->redirect("index.php?route=list-contact&success=contact_deleted");
        } else {
            $this->redirect("index.php?route=list-contact&error=contact_not_found");
        }
    }
}
