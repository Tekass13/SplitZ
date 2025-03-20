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
            $lastInsertId = $this->db->lastInsertId();
            $user->setId($data["id"]);

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
            $lastInsertId = $this->db->lastInsertId();
            $user->setId($data["id"]);

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
            $user->setId($data["id"]);
            return $user;
        } else {
            return null;
        }
    }

    public function getAllUsers(): array
    {
        $query = $this->db->query('SELECT * FROM users ORDER BY username');
        $users = [];

        while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User(
                $data["username"],
                $data["email"],
                $data["password"],
                $data["role"],
                $data["created_at"],
            );
            $user->setId($data["id"]);
            $users[] = $user;
        }

        return $users;
    }

    public function create(User $user): bool
    {
        $query = $this->db->prepare('INSERT INTO users (username, email, password, role, created_at) VALUES (:username, :email, :password, "USER", NOW())');

        $parameters = [
            ':username' => $user->getUserName(),
            ':email' => $user->getEmail(),
            ':password' => password_hash($user->getPassword(), PASSWORD_BCRYPT)
        ];

        $user->setId($this->db->lastInsertId());

        return $query->execute($parameters);
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
