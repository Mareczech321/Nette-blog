<?php
declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\CommentDTO;

class CommentFormMapper
{
    /**
     * @param array<string, mixed> $data
     */
    public function mapFormToDTO(
        array $data,
        int $postId,
        ?int $userId,
        ?int $parentId
    ): CommentDTO {

        $name = (isset($data['name']) && is_string($data['name'])) ? $data['name'] : 'Anonym';
        $email = (isset($data['email']) && is_string($data['email'])) ? $data['email'] : 'anonym@example.com';
        $content = (isset($data['content']) && is_string($data['content'])) ? $data['content'] : '';

        return new CommentDTO(
            id: 0,
            postId: $postId,
            name: $name,
            email: $email,
            content: $content,
            createdAt: new \DateTimeImmutable(),
            likeCountProvider: fn(): int => 0,
            userId: $userId,
            parentId: $parentId
        );
    }
}