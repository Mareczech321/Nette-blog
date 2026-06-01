<?php
declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\PostDTO;
use App\Model\PostFacade;
use Nette\Database\Table\ActiveRow;

class PostMapper
{
    public function __construct(private PostFacade $postFacade) {}

    public function map(ActiveRow $row): PostDTO
    {
        $id = is_scalar($row->id) ? (int)$row->id : 0;

        $likeProvider = fn(): int => $this->postFacade->getLikeCount($id);

        return new PostDTO(
            id: $id,
            title: is_scalar($row->title) ? (string)$row->title : '',
            content: is_scalar($row->content) ? (string)$row->content : '',
            createdAt: $row->created_at instanceof \DateTimeInterface ? $row->created_at : null,
            likeCountProvider: $likeProvider,
            isPremium: isset($row->is_premium) ? (bool)$row->is_premium : false,
            imageName: is_scalar($row->image) ? (string)$row->image : null,
            userId: is_scalar($row->user_id) ? (int)$row->user_id : null
        );
    }
}