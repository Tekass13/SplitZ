<?php

class MessageController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            return;
        }
    }
    
    // Afficher la boîte de réception
    public function inbox(): void
    {
        $userId = $_SESSION['user'];
        $messageManager = new MessageManager();
        $messages = $messageManager->getInboxMessages($userId);
        $unreadCount = $messageManager->countUnreadMessages($userId);
        
        $CSRFTokenManager = new CSRFTokenManager();
        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $CSRFTokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }
        
        $this->render("inbox", [
            'messages' => $messages, 
            'unreadCount' => $unreadCount,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }
    
    // Afficher un message
    public function viewMessage(): void
    {
        if (!isset($_GET['id'])) {
            $this->redirect("index.php?route=inbox");
            return;
        }
        
        $messageId = (int)$_GET['id'];
        $userId = $_SESSION['user'];
        $messageManager = new MessageManager();
        $message = $messageManager->getMessage($messageId);
        
        // Vérifier que le message existe et appartient à l'utilisateur connecté
        if (!$message || $message->getRecipientId() !== $userId) {
            $this->redirect("index.php?route=inbox");
            return;
        }
        
        // Marquer le message comme lu
        $messageManager->markAsRead($messageId);
        
        $userManager = new UserManager();
        $sender = $userManager->findById($message->getSenderId());
        
        $CSRFTokenManager = new CSRFTokenManager();
        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $CSRFTokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }
        
        $this->render("view-message", [
            'message' => $message,
            'sender' => $sender,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }
    
    // Rediger un message ou une réponse
    public function composeMessage(): void
    {
        $userManager = new UserManager();
        $users = $userManager->getAllUsers();

        $CSRFTokenManager = new CSRFTokenManager();
        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $CSRFTokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        if (isset($_GET['reply_to'])) {
            $messageId = (int)$_GET['reply_to'];
            $messageManager = new MessageManager();
            $message = $messageManager->getMessage($messageId);
            $this->render("compose-message", ['message' => $message]);
        } else {
            $this->render("compose-message", [
                'users' => $users,
                'csrf_token' => $_SESSION['csrf_token'],
            ]);
        }
        
    }
    
    // Envoyer un message
    public function sendMessage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?route=compose-message");
            return;
        }
        
        // Vérification du token CSRF
        $CSRFTokenManager = new CSRFTokenManager();
        $token = $_POST['csrf_token'] ?? '';
        if (!$CSRFTokenManager->validateCSRFToken($token)) {
            die("Erreur CSRF.");
        }
        
        // Récupération des données du formulaire
        $recipientId = (int)($_POST['recipient_id'] ?? 0);
        $subject = htmlspecialchars($_POST['subject'] ?? '');
        $content = htmlspecialchars($_POST['content'] ?? '');
        $senderId = $_SESSION['user'];
        
        // Validation des données
        if ($recipientId === 0 || empty($subject) || empty($content)) {
            $this->redirect("index.php?route=compose-message&error=empty_fields");
            return;
        }
        
        // Création et envoi du message
        $message = new Message($senderId, $recipientId, $subject, $content);
        $messageManager = new MessageManager();
        $result = $messageManager->createMessage($message);
        
        if ($result) {
            $this->redirect("index.php?route=inbox&success=message_sent");
        } else {
            $this->redirect("index.php?route=compose-message&error=send_failed");
        }
    }
    
    // Supprimer un message
    public function deleteMessage(): void
    {
        if (!isset($_GET['id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?route=inbox");
            return;
        }
        
        // Vérification du token CSRF
        $CSRFTokenManager = new CSRFTokenManager();
        $token = $_POST['csrf_token'] ?? '';
        if (!$CSRFTokenManager->validateCSRFToken($token)) {
            die("Erreur CSRF.");
        }
        
        $messageId = (int)$_GET['id'];
        $userId = $_SESSION['user'];
        $messageManager = new MessageManager();
        $message = $messageManager->getMessage($messageId);
        
        // Vérifier que le message existe et appartient à l'utilisateur connecté
        if (!$message || $message->getRecipientId() !== $userId) {
            $this->redirect("index.php?route=inbox");
            return;
        }
        
        $result = $messageManager->deleteMessage($messageId);
        
        if ($result) {
            $this->redirect("index.php?route=inbox&success=message_deleted");
        } else {
            $this->redirect("index.php?route=inbox&error=delete_failed");
        }
    }
}