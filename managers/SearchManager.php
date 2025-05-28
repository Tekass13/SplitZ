<?php
class SearchManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllUsers(int $userId): array
    {
        $query = $this->db->prepare("SELECT * FROM users WHERE id != :user_id");

        $parameters = [
            ':user_id' => $userId
        ];

        $query->execute($parameters);
        return $query->fetchAll(PDO::FETCH_ASSOC);
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

    public function searchUsers(string $searchTerm, int $userId): array
    {

        $query = $this->db->prepare("
            SELECT * 
            FROM users 
            WHERE (username LIKE :search OR email LIKE :search) 
            AND id != :user_id 
            AND id NOT IN (
                SELECT contact_id FROM contacts_list WHERE user_id = :user_id
            )
        ");

        $parameters = [
            ':search' => '%' . $searchTerm . '%',
            ':user_id' => $userId
        ];

        $query->execute($parameters);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchContacts(string $searchTerm, int $userId): array
    {
        $query = $this->db->prepare("
            SELECT * 
            FROM users 
            WHERE (username LIKE :search OR email LIKE :search) 
            AND id != :user_id 
            AND id IN (
                SELECT contact_id FROM contacts_list WHERE user_id = :user_id
            )
        ");

        $parameters = [
            ':search' => '%' . $searchTerm . '%',
            ':user_id' => $userId
        ];

        $query->execute($parameters);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
