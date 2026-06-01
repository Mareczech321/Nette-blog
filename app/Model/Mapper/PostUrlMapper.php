<?php

namespace App\Model\Mapper;

use App\Model\DTO\PostDTO;

class PostUrlMapper
{
    public function __construct(){}

    /**
     * @param array<string, mixed> $rssData
     */
    public function mapRssToDTO(array $rssData, int $userId, ?string $imageName): PostDTO
    {
        $title = (isset($rssData['title']) && is_string($rssData['title'])) ? $rssData['title'] : 'Bez názvu';
        $content = (isset($rssData['description']) && is_string($rssData['description'])) ? $rssData['description'] : '';

        return new PostDTO(
            id: 0,
            title: $title,
            content: $content,
            createdAt: new \DateTimeImmutable(),
            likeCountProvider: fn(): int => 0,
            isPremium: false,
            imageName: $imageName,
            userId: $userId
        );
    }
}