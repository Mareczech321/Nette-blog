<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\CommentLikeDTO;
use App\Model\Enum\CommentLikeColumns;

class CommentLikeRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return CommentLikeColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var CommentLikeDTO $dto */
        return [
            CommentLikeColumns::COMMENT_ID->value => $dto->commentId,
            CommentLikeColumns::USER_ID->value => $dto->userId
        ];
    }

    public function isLikedBy(CommentLikeDTO $dto): bool
    {
        return $this->database->table($this->getTableName())
                ->where(CommentLikeColumns::COMMENT_ID->value, $dto->commentId)
                ->where(CommentLikeColumns::USER_ID->value, $dto->userId)
                ->fetch() !== null;
    }

    public function toggleLike(CommentLikeDTO $dto): void
    {
        $like = $this->database->table($this->getTableName())
            ->where(CommentLikeColumns::COMMENT_ID->value, $dto->commentId)
            ->where(CommentLikeColumns::USER_ID->value, $dto->userId)
            ->fetch();

        if ($like) {
            $like->delete();
        } else {
            $this->save(null, $dto);
        }
    }

    public function getLikeCount(int $commentId): int
    {
        return $this->database->table($this->getTableName())
            ->where(CommentLikeColumns::COMMENT_ID->value, $commentId)
            ->count();
    }
}