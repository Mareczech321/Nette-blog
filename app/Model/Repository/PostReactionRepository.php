<?php
declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\BaseRepository;
use App\Model\Enum\PostReactionColumns;

class PostReactionRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return PostReactionColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array {
        /** @var \App\Model\DTO\ReactionDTO $dto */
        return [
            PostReactionColumns::USER_ID->value => $dto->userId,
            PostReactionColumns::EMOJI->value => $dto->emoji,
            PostReactionColumns::POST_ID->value => $dto->postId,
        ];
    }

    /**
     * @return array<int, \Nette\Database\IRow>
     */
    public function getCounts(int $postId): array
    {
        $result = $this->database->query(
            'SELECT emoji, COUNT(*) AS count FROM ' . PostReactionColumns::TABLE_NAME->value . ' WHERE ' . PostReactionColumns::POST_ID->value . ' = ? GROUP BY emoji ORDER BY count DESC',
            $postId
        );
        
        $rows = [];
        foreach ($result as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function findByUserAndEmoji(int $postId, int $userId, string $emoji): ?\Nette\Database\Table\ActiveRow
    {
        return $this->database->table(PostReactionColumns::TABLE_NAME->value)
            ->where(PostReactionColumns::POST_ID->value, $postId)
            ->where(PostReactionColumns::USER_ID->value, $userId)
            ->where(PostReactionColumns::EMOJI->value, $emoji)
            ->fetch();
    }

    public function findByUser(int $postId, int $userId): ?\Nette\Database\Table\ActiveRow
    {
        return $this->database->table(PostReactionColumns::TABLE_NAME->value)
            ->where(PostReactionColumns::POST_ID->value, $postId)
            ->where(PostReactionColumns::USER_ID->value, $userId)
            ->fetch();
    }
}
