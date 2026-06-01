<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum CommentColumns: string
{
    case TABLE_NAME = 'comments';
    case POST_ID = 'post_id';
    case NAME = 'name';
    case EMAIL = 'email';
    case CONTENT = 'content';
    case USER_ID = 'user_id';
    case PARENT_ID = 'parent_id';
}
