<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\PostDTO;
use App\Model\Enum\PostColumns;

class PostRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return PostColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var PostDTO $dto */
        return [
            PostColumns::TITLE->value => $dto->title,
            PostColumns::CONTENT->value => $dto->content,
            PostColumns::IMAGE->value => $dto->imageName,
            PostColumns::USER_ID->value => $dto->userId,
            PostColumns::PREMIUM->value => $dto->isPremium
        ];
    }
}