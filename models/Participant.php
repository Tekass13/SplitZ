<?php
class Participant
{
    public int $id;
    public string $username;
    public float $amount;
    public string $invitation;
    public string $payment;
    public int $unique_id;

    public function __construct(string $username = '', float $amount = 0, string $invitation = 'En attente', string $payment = 'En attente', int $unique_id = 0)
    {
        $this->username = $username;
        $this->amount = $amount;
        $this->invitation = $invitation;
        $this->payment = $payment;
        $this->unique_id = $unique_id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getInvitation(): string
    {
        return $this->invitation;
    }

    public function setInvitation(string $invitation): void
    {
        $this->invitation = $invitation;
    }

    public function getPayment(): string
    {
        return $this->payment;
    }

    public function setPayment(string $payment): void
    {
        $this->payment = $payment;
    }

    public function getUniqueId(): string
    {
        return $this->unique_id;
    }

    public function setUniqueId(string $unique_id): void
    {
        $this->unique_id = $unique_id;
    }
}
