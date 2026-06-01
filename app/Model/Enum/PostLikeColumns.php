<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum PostLikeColumns: string
{
    case TABLE_NAME = 'post_likes';
    case POST_ID = 'post_id';
    case USER_ID = 'user_id';
}
