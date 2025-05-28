<?php

/**
 * Manager pour la gestion des opérations sur le profil utilisateur dans la base de données
 */
class ProfilManager extends AbstractManager
{
    /**
     * Récupère un utilisateur par son ID
     * 
     * @param int $id ID de l'utilisateur
     * @return ?User L'utilisateur trouvé ou null si non trouvé
     */
    public function getUserById(int $id): ?User
    {
        $query = $this->db->prepare("
            SELECT *
            FROM users 
            WHERE id = :id
        ");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $userData = $query->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        $user = new User(
            $userData['username'],
            $userData['email'],
            $userData['password'],
        );
        $user->setId($userData['id']);

        if (isset($userData['unique_id'])) {
            $user->setUniqueId($userData['unique_id']);
        }

        return $user;
    }

    /**
     * Met à jour le nom d'utilisateur
     * 
     * @param int $id ID de l'utilisateur
     * @param string $username Nouveau nom d'utilisateur
     * @return bool Succès de l'opération
     */
    public function updateUsername(int $id, string $username): bool
    {
        $query = $this->db->prepare(" UPDATE users SET username = :username WHERE id = :id ");
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);

        return $query->execute();
    }

    /**
     * Met à jour l'email de l'utilisateur
     * 
     * @param int $id ID de l'utilisateur
     * @param string $email Nouvel email
     * @return bool Succès de l'opération
     */
    public function updateEmail(int $id, string $email): bool
    {
        $query = $this->db->prepare("UPDATE users SET email = :email WHERE id = :id");
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);

        return $query->execute();
    }
}
