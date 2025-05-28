<?php

class User
{
    private ?int $id = null;
    private ?int $uniqueId = 0;
    private string $created_at = '';

    public function __construct(private ?string $username = "", private ?string $email = "", private ?string $password = "", private ?string $role = "USER")
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
        return;
    }

    public function getUserName(): string
    {
        return $this->username;
    }

    public function setUserName(string $username): void
    {
        $this->username = $username;
        return;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        return;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
        return;
    }

    public function getHashedPassword(): string
    {
        return password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function setHashedPassword(string $password): void
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        return;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
        return;
    }

    public function getUniqueId(): int
    {
        return $this->uniqueId;
    }

    public function setUniqueId(int $uniqueId): void
    {
        $this->uniqueId = $uniqueId;
        return;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at = $created_at ?? date('Y-m-d H:i:s');
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->created_at = $createdAt;
        return;
    }
}
