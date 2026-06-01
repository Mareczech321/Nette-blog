<?php
    declare(strict_types=1);

    namespace App\Model\DTO;

    class PostDTO{
        public function __construct(
            public int $id,
            public string $title,
            public string $content,
            public ?\DateTimeInterface $createdAt,
            private \Closure $likeCountProvider,
            public bool $isPremium = false,
            public ?string $imageName = null,
            public ?int $userId = null
        ){}

        public function getLikeCount(): int
        {
            return ($this->likeCountProvider)();
        }
    }