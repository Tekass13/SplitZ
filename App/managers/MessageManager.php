<?php

class MessageManager extends AbstractManager
{
    public function __construct() {
        parent::__construct();
    }
    
    // Récupérer tous les messages d'un utilisateur
    public function getInboxMessages(int $userId): array
    {
        $query = $this->db->prepare('
            SELECT m.*, u.username as sender_name 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE m.recipient_id = :userId 
            ORDER BY m.created_at DESC
        ');
        $parameters = [':userId' => $userId];
        $query->execute($parameters);
        
        $messages = [];
        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
        $message = new Message(
            $data['sender_id'],
            $data['recipient_id'],
            $data['subject'],
            $data['content'],
            (bool)$data['is_read'],
            $data['created_at']
        );
        $message->setId($data['id']);
        $message->setSenderName($data['sender_name']);
            $messages[] = $message;
        }
        
        return $messages;
    }
    
    // Récupérer un message spécifique
    public function getMessage(int $messageId): ?Message
    {
        $query = $this->db->prepare('SELECT * FROM messages WHERE id = :id');
        $parameters = [':id' => $messageId];
        $query->execute($parameters);
        
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $message = new Message(
                $data['sender_id'],
                $data['recipient_id'],
                $data['subject'],
                $data['content'],
                (bool)$data['is_read'],
                $data['created_at']
            );
            $message->setId($data['id']);
            return $message;
        }
        
        return null;
    }
    
    // Créer un nouveau message
    public function createMessage(Message $message): bool
    {
        $query = $this->db->prepare('
            INSERT INTO messages (sender_id, recipient_id, subject, content, is_read, created_at)
            VALUES (:sender_id, :recipient_id, :subject, :content, :is_read, NOW())
        ');
        
        $parameters = [
            ':sender_id' => $message->getSenderId(),
            ':recipient_id' => $message->getRecipientId(),
            ':subject' => $message->getSubject(),
            ':content' => $message->getContent(),
            ':is_read' => $message->isRead() ? 1 : 0
        ];
        
        $result = $query->execute($parameters);
        if ($result) {
            $message->setId($this->db->lastInsertId());
        }
        
        return $result;
    }
    
    // Marquer un message comme lu
    public function markAsRead(int $messageId): bool
    {
        $query = $this->db->prepare('UPDATE messages SET is_read = 1 WHERE id = :id');
        $parameters = [':id' => $messageId];
        return $query->execute($parameters);
    }
    
    // Supprimer un message
    public function deleteMessage(int $messageId): bool
    {
        $query = $this->db->prepare('DELETE FROM messages WHERE id = :id');
        $parameters = [':id' => $messageId];
        return $query->execute($parameters);
    }
    
    // Compter les messages non lus
    public function countUnreadMessages(int $userId): int
    {
        $query = $this->db->prepare('
            SELECT COUNT(*) as count 
            FROM messages 
            WHERE recipient_id = :userId AND is_read = 0
        ');
        $parameters = [':userId' => $userId];
        $query->execute($parameters);
        
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }
}