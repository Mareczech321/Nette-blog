<?php
    declare(strict_types=1);

    namespace App\Model\DTO;

    class CommentDTO{
        public function __construct(
            public int $id,
            public int $postId,
            public string $name,
            public string $email,
            public string $content,
            public ?\DateTimeInterface $createdAt,
            private \Closure $likeCountProvider,
            public ?int $userId = null,
            public ?int $parentId = null
        ) {}

        public function getLikeCount(): int
        {
            return ($this->likeCountProvider)();
        }
    }