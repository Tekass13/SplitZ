<?php

class ContactController extends AbstractController
{

    public function __construct()
    {
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
                return;
            }

            $contactManager = new ContactManager();

            if ($contactManager->insertContact($userId, $identifier)) {
                $_SESSION['message'] = "Contact ajouté !";
                $this->redirect('index.php?route=search-user&status=success');
            } else {
                $_SESSION['message'] = "Erreur lors de l'ajout";
                $this->redirect('index.php?route=search-user&status=errors');
            }
        } else {
            $this->showContacts();
        }
    }

    public function showContacts(): void
    {
        $message = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }

        $userId = $_SESSION['user'];

        $contactManager = new ContactManager();
        $contacts = $contactManager->getAllContacts($userId);

        $this->render("list-contact", [
            "contacts" => $contacts,
            "message" => $message
        ]);
    }

    public function deleteContact(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['id'])) {
            $this->redirect("index.php?route=list-contact&error=invalid_request");
            return;
        }

        $contactId = $_GET['id'];
        $userId = $_SESSION['user'];
        $_SESSION['message'] = "Contact supprimé !";

        if ($contactId <= 0) {
            $this->redirect("index.php?route=list-contact&error=invalid_contact");
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        $tokenManager = new CSRFTokenManager();

        if (!$tokenManager->validateCSRFToken($token)) {
            die("Erreur CSRF.");
        }

        $contactManager = new ContactManager();
        $success = $contactManager->deleteContact($userId, $contactId);

        if ($success) {
            $this->redirect("index.php?route=list-contact&success=contact_deleted");
        } else {
            $this->redirect("index.php?route=list-contact&error=contact_not_found");
        }
    }
}
