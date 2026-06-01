<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum CommentLikeColumns: string
{
    case TABLE_NAME = 'comment_likes';
    case COMMENT_ID = 'comment_id';
    case USER_ID = 'user_id';
}
