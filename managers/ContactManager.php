<?php
class ContactManager extends AbstractManager
{

    public function __construct()
    {
        parent::__construct();
    }

    public function insertContact(int $userId, string $identifier): bool
    {
        $query = $this->db->prepare("SELECT id FROM users WHERE username = :identifier OR email = :identifier LIMIT 1");
        $query->execute([':identifier' => trim($identifier)]);
        $contact = $query->fetch(PDO::FETCH_ASSOC);

        if (!$contact) {
            return false;
        }

        $contactId = (int) $contact['id'];

        $query = $this->db->prepare("SELECT 1 FROM contacts_list WHERE user_id = :user_id AND contact_id = :contact_id LIMIT 1");
        $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

        if ($query->fetch()) {
            return false;
        }

        $query = $this->db->prepare("INSERT INTO contacts_list (user_id, contact_id) VALUES (:user_id, :contact_id)");
        return $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);
    }

    public function getAllContacts(int $userId): array
    {
        $query = $this->db->prepare("
            SELECT * 
            FROM users 
            WHERE id != :user_id 
            AND id IN (
                SELECT contact_id FROM contacts_list WHERE user_id = :user_id
            )
        ");

        $parameters = [
            ':user_id' => $userId
        ];

        $query->execute($parameters);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContactById(int $userId, int $contactId): array
    {

        $query = $this->db->prepare("
            SELECT users.id, users.username, users.email
            FROM users
            JOIN contacts_list ON users.id = contacts_list.contact_id
            WHERE contacts_list.user_id = :user_id AND contacts_list.contact_id = :contact_id
            ORDER BY users.username ASC
            LIMIT 1
        ");
        $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

        return $query->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function deleteContact(int $userId, int $contactId): bool
    {

        $query = $this->db->prepare("SELECT 1 FROM contacts_list WHERE user_id = :user_id AND contact_id = :contact_id LIMIT 1");
        $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

        if (!$query->fetch()) {
            return false;
        }

        $query = $this->db->prepare("DELETE FROM contacts_list WHERE user_id = :user_id AND contact_id = :contact_id");
        $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

        return true;
    }
}
