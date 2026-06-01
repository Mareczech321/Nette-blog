<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum CommentReactionColumns: string
{
    case TABLE_NAME = 'comment_reactions';
    case COMMENT_ID = 'comment_id';
    case USER_ID = 'user_id';
    case EMOJI = 'emoji';
}
