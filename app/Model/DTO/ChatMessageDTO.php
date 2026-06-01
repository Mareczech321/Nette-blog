<?php
declare(strict_types=1);

namespace App\Model\DTO;

class ChatMessageDTO
{
    public function __construct(
        public int $senderId,
        public int $receiverId,
        public string $content,
        public ?string $encryptedContent = null,
        public ?string $encryptionIv = null,
        public ?string $encryptionTag = null,
    ) {}

    public function isEncrypted(): bool
    {
        return $this->encryptedContent !== null
            && $this->encryptionIv !== null
            && $this->encryptionTag !== null;
    }
}