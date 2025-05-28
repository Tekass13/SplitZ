<?php

class UserManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findByEmail(string $email): ?User
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $parameters = [':email' => $email];
        $query->execute($parameters);

        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $user = new User(
                $data["username"],
                $data["email"],
                $data["password"],
                $data["role"],
                $data["created_at"],
            );
            $this->db->lastInsertId();
            $user->setId($data["id"]);
            $user->setUniqueId($data["unique_id"]);

            return $user;
        } else {
            return null;
        }
    }

    public function findByUsername(string $searchTerm): ?User
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE username LIKE :username');
        $parameters = [':username' => $searchTerm];
        $query->execute($parameters);

        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $user = new User(
                $data["username"],
                $data["email"],
                $data["password"],
                $data["role"],
                $data["created_at"],
            );
            $this->db->lastInsertId();
            $user->setId($data["id"]);
            $user->setUniqueId($data["unique_id"]);

            return $user;
        } else {
            return null;
        }
    }

    public function findById(int $id): ?User
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $parameters = [':id' => $id];
        $query->execute($parameters);

        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $user = new User(
                $data["username"],
                $data["email"],
                $data["password"],
                $data["role"],
                $data["created_at"],
            );
            $this->db->lastInsertId();
            $user->setId($data["id"]);
            $user->setUniqueId($data["unique_id"]);

            return $user;
        } else {
            return null;
        }
    }

    public function getAllUsers(): array
    {
        $userId = $_SESSION['user'];

        $query = $this->db->prepare('SELECT * FROM users WHERE NOT users.id = :user_id ORDER BY username');
        $parameters = [':user_id' => $userId];
        $query->execute($parameters);

        return $query->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function create(User $user): bool
    {
        $query = $this->db->prepare('INSERT INTO users (username, email, password, role, unique_id, created_at) VALUES (:username, :email, :password, "USER", :unique_id, NOW())');

        $tempUniqueId = rand(1000, 1500);
        $user->setUniqueId($tempUniqueId);

        $parameters = [
            ':username' => $user->getUserName(),
            ':email' => $user->getEmail(),
            ':password' => password_hash($user->getPassword(), PASSWORD_BCRYPT),
            ':unique_id' => $user->getUniqueId()
        ];

        $result = $query->execute($parameters);

        if ($result) {
            $user->setId($this->db->lastInsertId());
            $finalUniqueId = $user->getId() + rand(1000, 1500);
            $user->setUniqueId($finalUniqueId);

            $updateQuery = $this->db->prepare('UPDATE users SET unique_id = :unique_id WHERE id = :id');
            $updateParams = [
                ':unique_id' => $user->getUniqueId(),
                ':id' => $user->getId()
            ];
            $updateQuery->execute($updateParams);
        }

        return $result;
    }

    public function updatePassword(User $user): bool
    {
        $query = $this->db->prepare('UPDATE users SET password = :password WHERE email = :email');
        $parameters = [
            ':email' => $user->getEmail(),
            ':password' => password_hash($user->getPassword(), PASSWORD_BCRYPT)
        ];
        return $query->execute($parameters);
    }
}
