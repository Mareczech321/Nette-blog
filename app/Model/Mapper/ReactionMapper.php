<?php
declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\ReactionDTO;
use Nette\Database\Table\ActiveRow;

class ReactionMapper
{
    public function map(ActiveRow $row): ReactionDTO
    {
        /** @var int $id */
        $id = $row->id;
        /** @var int $userId */
        $userId = $row->user_id;
        /** @var string $emoji */
        $emoji = $row->emoji;
        /** @var int|null $postId */
        $postId = $row->post_id ?? null;
        /** @var int|null $commentId */
        $commentId = $row->comment_id ?? null;

        return new ReactionDTO(
            $id,
            $userId,
            $emoji,
            $postId ? (int)$postId : null,
            $commentId ? (int)$commentId : null
        );
    }
}