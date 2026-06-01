<?php
namespace App\Model;

use App\Model\DTO\PostDTO;
use App\Model\DTO\CommentDTO;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class PostFacade
{
    public function __construct(
        private PostRepository $postRepository,
        private CommentRepository $commentRepository,
        private Explorer $database,
        private PostLikeRepository $postLikeRepository
    ) {}

    public function savePost(?int $id, PostDTO $dto): ActiveRow{
        return $this->postRepository->save($id, $dto);
    }

    public function deletePostAndComments(int $postId): void{
        $this->database->beginTransaction();

        try{
            $this->commentRepository->deleteByPostId($postId);
            $this->postRepository->delete($postId);
            $this->database->commit();
        }catch (\Exception $e){
            $this->database->rollBack();
            throw $e;
        }
    }

    public function getAllPosts(): Selection
    {
        return $this->postRepository->findAll();
    }

    public function getPost(int $id): ?ActiveRow
    {
        return $this->postRepository->findById($id);
    }

    public function getCommentsForPost(int $postId): Selection
    {
        return $this->commentRepository->findAll()->where('post_id', $postId);
    }

    public function saveComment(CommentDTO $dto): ActiveRow
    {
        return $this->commentRepository->save(null, $dto);
    }

    public function deleteComment(int $commentId): void
    {
        $this->commentRepository->delete($commentId);
    }

    public function getComment(int $id): ?ActiveRow{
        return $this->commentRepository->findById($id);
    }

    public function isLikedBy(\App\Model\DTO\PostLikeDTO $dto): bool
    {
        return $this->postLikeRepository->isLikedBy($dto);
    }

    public function toggleLike(\App\Model\DTO\PostLikeDTO $dto): void
    {
        $this->postLikeRepository->toggleLike($dto);
    }

    public function getLikeCount(int $postId): int
    {
        return $this->postLikeRepository->getLikeCount($postId);
    }

    public function getPostsCount(): int
    {
        return $this->database->table('posts')->count();
    }

    /**
     * @return array<\Nette\Database\Table\ActiveRow>
     */
    public function getPublicPosts(int $limit, int $offset): array{
        $safeLimit = max(0, $limit);
        $safeOffset = max(0, $offset);

        return $this->database->table('posts')
            ->order('created_at DESC')
            ->limit($safeLimit, $safeOffset)
            ->fetchAll();
    }

    /**
     * @return array<\Nette\Database\Table\ActiveRow>
     */
    public function getAllComments(): array {
        return $this->database->table('comments')->fetchAll();
    }
}