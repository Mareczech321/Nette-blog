<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum PostReactionColumns: string
{
    case TABLE_NAME = 'post_reactions';
    case POST_ID = 'post_id';
    case USER_ID = 'user_id';
    case EMOJI = 'emoji';
}
