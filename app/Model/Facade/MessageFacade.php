<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\ChatMessageDTO;
use App\Model\Repository\MessageRepository;
use App\Model\Service\EncryptionService;

class MessageFacade
{
    public function __construct(
        private MessageRepository $messageRepository,
        private EncryptionService $encryptionService,
        private string $appSecret
    ) {}

    /**
     * @return array<\stdClass>
     */
    public function getMessages(int $currentUser, int $otherUser): array
    {
        $rows = $this->messageRepository->getConversation($currentUser, $otherUser)->fetchAll();
        $key = $this->encryptionService->generateConversationKey($currentUser, $otherUser, $this->appSecret);

        $decryptedMessages = [];
        foreach ($rows as $row) {
            $decryptedMessages[] = $this->decryptMessageRow((object)$row->toArray(), $key);
        }
        return $decryptedMessages;
    }

    public function sendMessage(ChatMessageDTO $dto): void
    {
        if (trim($dto->content) === '') {
            return;
        }

        $this->messageRepository->sendMessage($dto);
    }

    /**
     * @return array<object>
     */
    public function getUserConversations(int $userId): array
    {
        return $this->messageRepository->getUserConversations($userId);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->messageRepository->getUnreadCount($userId);
    }

    public function markAsRead(int $senderId, int $receiverId): void
    {
        $this->messageRepository->markAsRead($senderId, $receiverId);
    }

    /**
     * Decrypt a message row from database
     */
    private function decryptMessageRow(\stdClass $row, string $key): \stdClass
    {
        $decrypted = clone $row;

        if (isset($row->encrypted_content) && $row->encrypted_content &&
            isset($row->encryption_iv) && $row->encryption_iv &&
            isset($row->encryption_tag) && $row->encryption_tag) {
            try {
                $content = $this->encryptionService->decrypt(
                    $row->encrypted_content,
                    $row->encryption_iv,
                    $row->encryption_tag,
                    $key
                );
                $decrypted->content = trim($content);
            } catch (\RuntimeException $e) {
                $decrypted->content = '[Chyba při dešifrování zprávy]';
            }
        }

        return $decrypted;
    }
}