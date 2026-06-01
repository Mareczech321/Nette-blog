<?php
declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\BaseRepository;
use App\Model\DTO\ChatMessageDTO;
use App\Model\Enum\MessageColumns;
use App\Model\Service\EncryptionService;

class MessageRepository extends BaseRepository
{
    public function __construct(
        \Nette\Database\Explorer $explorer,
        private EncryptionService $encryptionService,
        private string $appSecret
    ) {
        parent::__construct($explorer);
    }

    public function getConversation(int $user1, int $user2): \Nette\Database\Table\Selection
    {
        return $this->database->table(MessageColumns::TABLE_NAME->value)
            ->where('(' . MessageColumns::SENDER_ID->value . ' = ? AND ' . MessageColumns::RECEIVER_ID->value . ' = ?) OR (' . MessageColumns::SENDER_ID->value . ' = ? AND ' . MessageColumns::RECEIVER_ID->value . ' = ?)',
                $user1, $user2, $user2, $user1)
            ->order(MessageColumns::CREATED_AT->value . ' ASC');
    }

    public function sendMessage(ChatMessageDTO $dto): void
    {
        $this->save(null, $dto);
    }

    protected function getTableName(): string
    {
        return MessageColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var ChatMessageDTO $dto */
        $encryptedData = null;
        $key = $this->encryptionService->generateConversationKey(
            $dto->senderId,
            $dto->receiverId,
            $this->appSecret
        );

        if ($dto->content) {
            $encryptedData = $this->encryptionService->encrypt($dto->content, $key);
        }

        $data = [
            MessageColumns::SENDER_ID->value => $dto->senderId,
            MessageColumns::RECEIVER_ID->value => $dto->receiverId,
            MessageColumns::CONTENT->value => null,
            MessageColumns::CREATED_AT->value => new \DateTime()
        ];

        if ($encryptedData) {
            $data[MessageColumns::ENCRYPTED_CONTENT->value] = $encryptedData['encrypted'];
            $data[MessageColumns::ENCRYPTION_IV->value] = $encryptedData['iv'];
            $data[MessageColumns::ENCRYPTION_TAG->value] = $encryptedData['tag'];
        }

        return $data;
    }

    /**
     * @return array<object>
     */
    public function getUserConversations(int $userId): array
    {
        $messages = $this->database->table(MessageColumns::TABLE_NAME->value)
            ->where(MessageColumns::SENDER_ID->value . ' = ? OR ' . MessageColumns::RECEIVER_ID->value . ' = ?', $userId, $userId)
            ->order(MessageColumns::CREATED_AT->value . ' DESC')
            ->fetchAll();

        $contactIds = [];
        foreach ($messages as $msg) {
            $senderId = (int) $msg->{MessageColumns::SENDER_ID->value};
            $receiverId = (int) $msg->{MessageColumns::RECEIVER_ID->value};
            $otherId = ($senderId === $userId) ? $receiverId : $senderId;

            if (!in_array($otherId, $contactIds, true)) {
                $contactIds[] = $otherId;
            }
        }

        if (empty($contactIds)) {
            return [];
        }

        $users = $this->database->table('users')->where('id', $contactIds)->fetchAll();

        $conversations = [];

        foreach ($contactIds as $cid) {
            if (!isset($users[$cid])) continue;

            $userRow = $users[$cid];

            $unreadCount = (int) $this->database->table(MessageColumns::TABLE_NAME->value)
                ->where(MessageColumns::SENDER_ID->value, $cid)
                ->where(MessageColumns::RECEIVER_ID->value, $userId)
                ->where(MessageColumns::IS_READ->value, 0)
                ->count('id');

            $conversations[] = (object) [
                'id' => $userRow->id,
                'username' => $userRow->username,
                'unreadCount' => $unreadCount
            ];
        }

        return $conversations;
    }

    public function getUnreadCount(int $userId): int
    {
        return (int) $this->database->table(MessageColumns::TABLE_NAME->value)
            ->where(MessageColumns::RECEIVER_ID->value, $userId)
            ->where(MessageColumns::IS_READ->value, 0)
            ->count('id');
    }

    public function markAsRead(int $senderId, int $receiverId): void
    {
        $this->database->table(MessageColumns::TABLE_NAME->value)
            ->where(MessageColumns::SENDER_ID->value, $senderId)
            ->where(MessageColumns::RECEIVER_ID->value, $receiverId)
            ->where(MessageColumns::IS_READ->value, 0)
            ->update([MessageColumns::IS_READ->value => 1]);
    }
}