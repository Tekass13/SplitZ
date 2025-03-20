<?php

class MessageController extends AbstractController
{
    private UserManager $um;
    private CSRFTokenManager $csrfm;
    private MessageManager $mm;

    public function __construct()
    {
        $this->um = new UserManager();
        $this->csrfm = new CSRFTokenManager();
        $this->mm = new MessageManager();

        if (!isset($_SESSION['user'])) {
            return;
        }
    }

    public function inbox(): void
    {
        $userId = $_SESSION['user'];
        $messages = $this->mm->getInboxMessages($userId);
        $unreadCount = $this->mm->countUnreadMessages($userId);

        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $this->csrfm->generateCSRFToken();
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

    public function viewMessage(): void
    {
        if (!isset($_GET['id'])) {
            $this->redirect("index.php?route=inbox");
            return;
        }

        $messageId = (int)$_GET['id'];
        $userId = $_SESSION['user'];
        $message = $this->mm->getMessage($messageId);

        if (!$message || $message->getRecipientId() !== $userId) {
            $this->redirect("index.php?route=inbox");
            return;
        }

        $this->mm->markAsRead($messageId);

        $sender = $this->um->findById($message->getSenderId());

        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $this->csrfm->generateCSRFToken();
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
        $users = $this->um->getAllUsers();

        if (empty($_SESSION['csrf_token'])) {
            $csrfToken = $this->csrfm->generateCSRFToken();
            $_SESSION['csrf_token'] = $csrfToken;
        } else {
            $csrfToken = $_SESSION['csrf_token'];
        }

        if (isset($_GET['reply_to'])) {
            $messageId = (int)$_GET['reply_to'];
            $message = $this->mm->getMessage($messageId);
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
        if (!$this->csrfm->validateCSRFToken($token)) {
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
        $result = $this->mm->createMessage($message);

        if ($result) {
            $this->redirect("index.php?route=inbox&success=message_sent");
        } else {
            $this->redirect("index.php?route=compose-message&error=send_failed");
        }
    }

    public function deleteMessage(): void
    {
        if (!isset($_GET['id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("index.php?route=inbox");
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->csrfm->validateCSRFToken($token)) {
            die("Erreur CSRF.");
        }

        $messageId = (int)$_GET['id'];
        $userId = $_SESSION['user'];
        $message = $this->mm->getMessage($messageId);

        if (!$message || $message->getRecipientId() !== $userId) {
            $this->redirect("index.php?route=inbox");
            return;
        }

        $result = $this->mm->deleteMessage($messageId);

        if ($result) {
            $this->redirect("index.php?route=inbox&success=message_deleted");
        } else {
            $this->redirect("index.php?route=inbox&error=delete_failed");
        }
    }
}
