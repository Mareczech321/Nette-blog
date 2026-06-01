<?php
declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\CommentDTO;
use App\Model\DTO\PostDTO;
use App\Model\CommentFacade;
use Nette\Database\Table\ActiveRow;

class CommentMapper
{
    public function __construct(private CommentFacade $commentFacade) {}

    public function map(ActiveRow $row): CommentDTO
    {
        $id = is_scalar($row->id) ? (int)$row->id : 0;

        $likeProvider = fn(): int => $this->commentFacade->getLikeCount($id);

        return new CommentDTO(
            id: $id,
            postId: is_scalar($row->post_id) ? (int)$row->post_id : 0,
            name: is_scalar($row->name) ? (string)$row->name : 'Anonym',
            email: is_scalar($row->email) ? (string)$row->email : '',
            content: is_scalar($row->content) ? (string)$row->content : '',
            createdAt: $row->created_at instanceof \DateTimeInterface ? $row->created_at : null,
            likeCountProvider: $likeProvider,
            userId: is_scalar($row->user_id) ? (int)$row->user_id : null,
            parentId: is_scalar($row->parent_id) ? (int)$row->parent_id : null
        );
    }
}