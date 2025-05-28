<?php

class MessageController extends AbstractController
{

    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            return;
        }
    }

    public function home(): void
    {
        $userId = (int) $_SESSION['user'];
        $messageManager = new MessageManager();
        $messages = $messageManager->getInboxMessages($userId);
        $unreadCount = $messageManager->countUnreadMessages($userId);

        $tokenManager = new CSRFTokenManager();
        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $tokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        $this->render("home", [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function numberUnreadMessages()
    {
        if (isset($_SESSION['user'])) {
            $messageManager = new MessageManager();
            $unreadCount = $messageManager->countUnreadMessages((int) $_SESSION['user']);
            if ($unreadCount > 0) {
                return $unreadCount;
            }
        }
    }

    public function viewMessage(): void
    {
        if (!isset($_GET['id'])) {
            $this->redirect("index.php?route=home");
            return;
        }

        $messageId = (int)$_GET['id'];
        $userId = (int) $_SESSION['user'];
        $messageManager = new MessageManager();
        $message = $messageManager->getMessage($messageId);

        if (!$message || $message->getRecipientId() !== $userId) {
            $this->redirect("index.php?route=home");
            return;
        }

        $messageManager = new MessageManager();
        $messageManager->markAsRead($messageId);

        $userManager = new UserManager();
        $sender = $userManager->findById($message->getSenderId());

        if (empty($_SESSION['csrf_token'])) {
            $tokenManager = new CSRFTokenManager();
            $csrfToken = $tokenManager->generateCSRFToken();
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

    public function composeMessage(): void
    {
        $userManager = new UserManager();
        $users = $userManager->getAllUsers();

        if (empty($_SESSION['csrf_token'])) {
            $tokenManager = new CSRFTokenManager();
            $csrfToken = $tokenManager->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        if (isset($_GET['reply_to'])) {
            $messageId = (int)$_GET['reply_to'];
            $messageManager = new MessageManager();
            $message = $messageManager->getMessage($messageId);
            $this->render("compose-message", [
                'message' => $message,
                'users' => $users,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        } else {
            $this->render("compose-message", [
                'users' => $users,
                'csrf_token' => $_SESSION['csrf_token'],
            ]);
        }
    }

    public function sendMessage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?route=compose-message");
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        $tokenManager = new CSRFTokenManager();

        if (!$tokenManager->validateCSRFToken($token)) {
            die("Erreur CSRF.");
        }

        $recipientId = (int)($_POST['recipient_id'] ?? 0);
        $subject = htmlspecialchars($_POST['subject'] ?? '');
        $content = htmlspecialchars($_POST['content'] ?? '');
        $senderId = $_SESSION['user'];

        if ($recipientId === 0 || empty($subject) || empty($content)) {
            $this->redirect("index.php?route=compose-message&error=empty_fields");
            return;
        }

        $message = new Message($senderId, $recipientId, $subject, $content);
        $messageManager = new MessageManager();
        $result = $messageManager->createMessage($message);

        if ($result) {
            $this->redirect("index.php?route=home&success=message_sent");
        } else {
            $this->redirect("index.php?route=compose-message&error=send_failed");
        }
    }

    public function deleteMessage(): void
    {
        if (!isset($_GET['id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?route=home");
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        $tokenManager = new CSRFTokenManager();

        if (!$tokenManager->validateCSRFToken($token)) {
            die("Erreur CSRF.");
        }

        $messageId = (int)$_GET['id'];
        $userId = $_SESSION['user'];

        $messageManager = new MessageManager();
        $message = $messageManager->getMessage($messageId);

        if (!$message || $message->getRecipientId() !== $userId) {
            $this->redirect("index.php?route=home");
            return;
        }

        $result = $messageManager->deleteMessage($messageId);

        if ($result) {
            $this->redirect("index.php?route=home&success=message_deleted");
        } else {
            $this->redirect("index.php?route=home&error=delete_failed");
        }
    }
}
