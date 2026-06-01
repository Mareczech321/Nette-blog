<?php
declare(strict_types=1);

namespace App\Model\DTO;

class CommentLikeDTO
{
    public function __construct(
        public int $commentId,
        public int $userId
    ) {}
}