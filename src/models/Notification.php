<?php

class Notification {
    private $id;
    private $message;
    private $is_read;
    private $created_at;

    public function __construct(
        string $message,
        bool $is_read = false,
        string $created_at = null,
        ?int $id = null
    ) {
        $this->message = $message;
        $this->is_read = $is_read;
        $this->created_at = $created_at ?? (new DateTime())->format('Y-m-d H:i:s');
        $this->id = $id;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function isRead(): bool {
        return $this->is_read;
    }

    public function markAsRead(): void {
        $this->is_read = true;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    
}
