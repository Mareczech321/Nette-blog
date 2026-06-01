<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\PostLikeDTO;
use App\Model\Enum\PostLikeColumns;

class PostLikeRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return PostLikeColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var PostLikeDTO $dto */
        return [
            PostLikeColumns::POST_ID->value => $dto->postId,
            PostLikeColumns::USER_ID->value => $dto->userId
        ];
    }

    public function isLikedBy(PostLikeDTO $dto): bool
    {
        return $this->database->table($this->getTableName())
                ->where(PostLikeColumns::POST_ID->value, $dto->postId)
                ->where(PostLikeColumns::USER_ID->value, $dto->userId)
                ->fetch() !== null;
    }

    public function toggleLike(PostLikeDTO $dto): void
    {
        $like = $this->database->table($this->getTableName())
            ->where(PostLikeColumns::POST_ID->value, $dto->postId)
            ->where(PostLikeColumns::USER_ID->value, $dto->userId)
            ->fetch();

        if ($like) {
            $like->delete();
        } else {
            $this->database->table($this->getTableName())->insert([
                PostLikeColumns::POST_ID->value => $dto->postId,
                PostLikeColumns::USER_ID->value => $dto->userId,
            ]);
        }
    }

    public function getLikeCount(int $postId): int
    {
        return $this->database->table($this->getTableName())
            ->where(PostLikeColumns::POST_ID->value, $postId)
            ->count();
    }
}