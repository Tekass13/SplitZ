<?php
class ContactManager extends AbstractManager {

    public function __construct() {
        parent::__construct();
    }

    public function insertContact(int $userId, string $identifier): bool {
        if ($userId <= 0 || empty($identifier) || strlen($identifier) > 255) {
            throw new Exception("Données invalides.");
        }

        try {
            // Vérifier si l'identifiant correspond à un username ou un email
            $query = $this->db->prepare("SELECT id FROM users WHERE username = :identifier OR email = :identifier LIMIT 1");
            $query->execute([':identifier' => trim($identifier)]);
            $contact = $query->fetch(PDO::FETCH_ASSOC);

            if (!$contact) {
                return false;
            }

            $contactId = (int) $contact['id'];

            // Vérifier si le contact existe déjà
            $query = $this->db->prepare("SELECT 1 FROM contacts_list WHERE user_id = :user_id AND contact_id = :contact_id LIMIT 1");
            $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

            if ($query->fetch()) {
                return false;
            }

            // Insérer dans contacts_list
            $query = $this->db->prepare("INSERT INTO contacts_list (user_id, contact_id) VALUES (:user_id, :contact_id)");
            return $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Une erreur est survenue lors de l'ajout du contact.");
        }
    }

    public function getAllContacts(int $userId): array {
        if ($userId <= 0) {
            throw new Exception("ID utilisateur invalide.");
        }

        try {
            $query = $this->db->prepare("
                SELECT users.id, users.username
                FROM users
                JOIN contacts_list ON users.id = contacts_list.contact_id
                WHERE contacts_list.user_id = :user_id
                ORDER BY users.username ASC
            ");
            $query->execute([':user_id' => $userId]);

            return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de récupérer les contacts.");
        }
    }

    public function getContactById(int $userId, int $contactId): array {
        if ($userId <= 0 || $contactId <= 0) {
            throw new Exception("Données invalides.");
        }

        try {
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
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de récupérer le contact.");
        }
    }

    public function deleteContact(int $userId, int $contactId) : bool {
        if ($userId <= 0 || $contactId <= 0) {
            throw new Exception("Données invalides.");
        }

        try {
            // Vérifier si l'utilisateur possède bien ce contact
            $query = $this->db->prepare("SELECT 1 FROM contacts_list WHERE user_id = :user_id AND contact_id = :contact_id LIMIT 1");
            $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

            if (!$query->fetch()) {
                return false;
            }

            // Supprimer le contact
            $query = $this->db->prepare("DELETE FROM contacts_list WHERE user_id = :user_id AND contact_id = :contact_id");
            $query->execute([':user_id' => $userId, ':contact_id' => $contactId]);

            return true;
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            throw new Exception("Impossible de supprimer le contact.");
        }
    }
}
?>
