<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\CommentDTO;
use App\Model\Enum\CommentColumns;

class CommentRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return CommentColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var CommentDTO $dto */
        return [
            CommentColumns::POST_ID->value => $dto->postId,
            CommentColumns::NAME->value => $dto->name,
            CommentColumns::EMAIL->value => $dto->email,
            CommentColumns::CONTENT->value => $dto->content,
            CommentColumns::USER_ID->value => $dto->userId,
            CommentColumns::PARENT_ID->value => $dto->parentId
        ];
    }

    public function deleteByPostId(int $postId): void
    {
        $this->database->table($this->getTableName())
            ->where(CommentColumns::POST_ID->value, $postId)
            ->delete();
    }
}