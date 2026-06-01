<?php
    declare(strict_types=1);

    namespace App\Model\DTO;

    class PostLikeDTO
    {
        public function __construct(
            public int $postId,
            public int $userId
        ) {}
    }