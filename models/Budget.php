<?php
class Budget
{
    public int $id;
    public string $title;
    public float $price;
    public string $created_at;
    public int $created_by;
    public string $created_by_username = '';
    public array $categories;

    public function __construct(string $title = '', float $price = 0.00, string $created_at = '', array $categories = [])
    {
        $this->title = $title;
        $this->price = $price;
        $this->created_at = $created_at;
        $this->categories = $categories;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getCreatedBy(): int
    {
        return $this->created_by;
    }

    public function setCreatedBy(int $created_by): void
    {
        $this->created_by = $created_by;
    }

    public function getCreatedByUsername(): string
    {
        return $this->created_by_username;
    }

    public function setCreatedByUsername(string $created_by_username): void
    {
        $this->created_by_username = $created_by_username;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }
}
