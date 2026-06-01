<?php
declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\ChatMessageDTO;
use App\Model\Repository\MessageRepository;

class MessageMapper
{

    public function __construct(
        private MessageRepository $messageRepository
    ){}

    public function toDTO(int $senderId, int $receiverId, \stdClass $values): ChatMessageDTO
    {
        return new ChatMessageDTO(
            senderId: $senderId,
            receiverId: $receiverId,
            content: $values->content
        );
    }

    /**
     * @return array<object>
     */
    public function getUserConversations(int $userId): array
    {
        return $this->messageRepository->getUserConversations($userId);
    }
}