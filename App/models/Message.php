<?php

class Message
{
    private ?int $id = null;
    private int $senderId;
    private int $recipientId;
    private string $subject;
    private string $content;
    private bool $isRead;
    private string $createdAt;
    private ?string $senderName = null;

    public function __construct(int $senderId = 0, int $recipientId = 0, string $subject = '', string $content = '', bool $isRead = false, string $createdAt = '')
    {
        $this->senderId = $senderId;
        $this->recipientId = $recipientId;
        $this->subject = $subject;
        $this->content = $content;
        $this->isRead = $isRead;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getSenderId(): int
    {
        return $this->senderId;
    }
    public function getSenderName(): ?string
    {
        return $this->senderName;
    }
    public function getRecipientId(): int
    {
        return $this->recipientId;
    }
    public function getSubject(): string
    {
        return $this->subject;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function isRead(): bool
    {
        return $this->isRead;
    }
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }


    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function setSenderId(int $senderId): void
    {
        $this->senderId = $senderId;
    }
    public function setRecipientId(int $recipientId): void
    {
        $this->recipientId = $recipientId;
    }
    public function setSenderName(string $senderName): void
    {
        $this->senderName = $senderName;
    }
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
    }
    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
