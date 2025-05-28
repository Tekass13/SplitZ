<?php
class Category
{
    public int $id;
    public string $type;
    public string $name;
    public float $price;
    public array $participants;

    public function __construct(string $type = '', string $name = '', float $price = 0.00, array $participants = [])
    {
        $this->type = $type;
        $this->name = $name;
        $this->price = $price;
        $this->participants = $participants;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getParticipants(): array
    {
        return $this->participants;
    }

    public function setParticipants(array $participants): void
    {
        $this->participants = $participants;
    }
}
