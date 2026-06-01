<?php

namespace App\Model;

use App\Model\DTO\CommentLikeDTO;

class CommentFacade
{
    public function __construct(
        private CommentLikeRepository $commentLikeRepository,
        private CommentRepository $commentRepository
    ) {}

    public function toggleLike(CommentLikeDTO $dto): void
    {
        $existing = $this->commentLikeRepository->findAll()
            ->where('comment_id', $dto->commentId)
            ->where('user_id', $dto->userId)
            ->fetch();

        if ($existing) {
            $existing->delete();
        } else {
            $this->commentLikeRepository->save(null, $dto);
        }
    }

    public function isLikedBy(CommentLikeDTO $dto): bool
    {
        return (bool) $this->commentLikeRepository->findAll()
            ->where('comment_id', $dto->commentId)
            ->where('user_id', $dto->userId)
            ->fetch();
    }

    public function getLikeCount(int $commentId): int
    {
        return $this->commentLikeRepository->findAll()
            ->where('comment_id', $commentId)
            ->count();
    }

    public function getAllComments(): \Nette\Database\Table\Selection
    {
        return $this->commentRepository->findAll();
    }
}