<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum PostColumns: string
{
    case TABLE_NAME = 'posts';
    case TITLE = 'title';
    case CONTENT = 'content';
    case IMAGE = 'image';
    case USER_ID = 'user_id';
    case PREMIUM = 'is_premium';
}
