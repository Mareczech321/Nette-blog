<?php
declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\BaseRepository;
use App\Model\Enum\CommentReactionColumns;

class CommentReactionRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return CommentReactionColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array {
        /** @var \App\Model\DTO\ReactionDTO $dto */
        return [
            CommentReactionColumns::USER_ID->value => $dto->userId,
            CommentReactionColumns::EMOJI->value => $dto->emoji,
            CommentReactionColumns::COMMENT_ID->value => $dto->commentId,
        ];
    }

    /**
     * @return array<int, \Nette\Database\IRow>
     */
    public function getCounts(int $commentId): array
    {
        $result = $this->database->query(
            'SELECT emoji, COUNT(*) AS count FROM ' . CommentReactionColumns::TABLE_NAME->value . ' WHERE ' . CommentReactionColumns::COMMENT_ID->value . ' = ? GROUP BY emoji ORDER BY count DESC',
            $commentId
        );
        
        $rows = [];
        foreach ($result as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function findByUserAndEmoji(int $commentId, int $userId, string $emoji): ?\Nette\Database\Table\ActiveRow
    {
        return $this->database->table(CommentReactionColumns::TABLE_NAME->value)
            ->where(CommentReactionColumns::COMMENT_ID->value, $commentId)
            ->where(CommentReactionColumns::USER_ID->value, $userId)
            ->where(CommentReactionColumns::EMOJI->value, $emoji)
            ->fetch();
    }

    public function findByUser(int $commentId, int $userId): ?\Nette\Database\Table\ActiveRow
    {
        return $this->database->table(CommentReactionColumns::TABLE_NAME->value)
            ->where(CommentReactionColumns::COMMENT_ID->value, $commentId)
            ->where(CommentReactionColumns::USER_ID->value, $userId)
            ->fetch();
    }
}
