<?php

namespace App\Model\Mapper;

use App\Model\DTO\PostDTO;
use App\Model\PostFacade;

class PostFormMapper
{
    public function __construct(
        private PostFacade $postFacade
    ){}
    /**
     * @param array<string, mixed> $data
     */
    public function mapFormToDTO(
        array $data,
        int $postId,
        ?string $passedImageName = null,
        ?int $passedUserId = null
    ): PostDTO {
        $title = (isset($data['title']) && is_string($data['title'])) ? $data['title'] : '';
        $content = (isset($data['content']) && is_string($data['content'])) ? $data['content'] : '';
        $isPremium = (isset($data['is_premium']) && is_bool($data['is_premium'])) ? $data['is_premium'] : false;

        $currentPost = ($postId > 0) ? $this->postFacade->getPost($postId) : null;

        $imageName = $passedImageName ?? ($currentPost && is_string($currentPost->image) ? $currentPost->image : null);
        $userId = $passedUserId ?? ($currentPost && is_numeric($currentPost->user_id) ? (int)$currentPost->user_id : null);

        return new PostDTO(
            id: $postId,
            title: $title,
            content: $content,
            createdAt: new \DateTimeImmutable(),
            likeCountProvider: fn(): int => 0,
            isPremium: $isPremium,
            imageName: $imageName,
            userId: $userId
        );
    }
}