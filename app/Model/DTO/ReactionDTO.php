<?php

namespace App\Model\DTO;

class ReactionDTO {
    public function __construct(
        public int $id,
        public int $userId,
        public string $emoji,
        public ?int $postId = null,
        public ?int $commentId = null
    ) {}
}