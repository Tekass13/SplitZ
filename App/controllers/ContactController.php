<?php

class ContactController extends AbstractController {
    private ContactManager $cm;

    public function __construct()
    {
        parent::__construct();
        $this->cm = new ContactManager();
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            return;
        }
    }

    public function addContact() : void {
        // Vérifier si la requête est bien de type POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user'];
            $identifier = filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_SPECIAL_CHARS);

            // Vérifier les données
            if (!$userId || !$identifier) {
                echo "Données invalides.";
                return;
            }

            // Vérifier l'état de la requête
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
        
        $CSRFTokenManager = new CSRFTokenManager();
        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $CSRFTokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }
        
        $users = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search-bar'])) {
            $searchTerm = htmlspecialchars($_POST['search-bar']);
            $searchCategory = $_POST['search-category'] ?? 'all';
            
            $userManager = new UserManager();
            
            $userId = $_SESSION['user'];
            
            // Adapter la recherche en fonction de la catégorie sélectionnée
            switch ($searchCategory) {
                case 'username':
                    $users = $userManager->searchUsersByUsername($searchTerm, $userId);
                    break;
                    
                case 'email':
                    $users = $userManager->searchUsersByEmail($searchTerm, $userId);
                    break;
                    
                case 'all':
                default:
                    $users = $userManager->searchUsers($searchTerm, $userId);
                    break;
            }
        }
        
        $this->render("search-result", [
            'users' => $users,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function showContacts() : void {   
         $userId = (int) $_SESSION['user'];
         $contacts = $this->cm->getAllContacts($userId);

         $this->render("list-contact", ["contacts" => $contacts]);
    }

    public function deleteContact() : void {
    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        $this->redirect("index.php?route=login");
        return;
    }
    
    // Vérifier si la requête est bien de type POST et qu'un ID est fourni
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['id'])) {
        $this->redirect("index.php?route=list-contact&error=invalid_request");
        return;
    }
    
    // Récupérer l'ID du contact à supprimer et l'ID de l'utilisateur connecté
    $contactId = (int)$_GET['id'];
    $userId = (int)$_SESSION['user'];
    
    // Vérifier que l'ID du contact est valide
    if ($contactId <= 0) {
        $this->redirect("index.php?route=list-contact&error=invalid_contact");
        return;
    }
    
    // Vérification du token CSRF
    $CSRFTokenManager = new CSRFTokenManager();
    $token = $_POST['csrf_token'] ?? '';
    if (!$CSRFTokenManager->validateCSRFToken($token)) {
        die("Erreur CSRF.");
    }
    
    try {
        // Appel à la méthode du manager pour supprimer le contact
        $contactManager = new ContactManager();
        $success = $contactManager->deleteContact($userId, $contactId);
        
        if ($success) {
            $this->redirect("index.php?route=list-contact&success=contact_deleted");
        } else {
            $this->redirect("index.php?route=list-contact&error=contact_not_found");
        }
    } catch (Exception $e) {
        error_log("Erreur lors de la suppression du contact: " . $e->getMessage());
        $this->redirect("index.php?route=list-contact&error=delete_failed");
    }
}

}
?>