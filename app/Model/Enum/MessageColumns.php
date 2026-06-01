<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum MessageColumns: string
{
    case TABLE_NAME = 'messages';
    case ID = 'id';
    case SENDER_ID = 'sender_id';
    case RECEIVER_ID = 'receiver_id';
    case CONTENT = 'content';
    case ENCRYPTED_CONTENT = 'encrypted_content';
    case ENCRYPTION_IV = 'encryption_iv';
    case ENCRYPTION_TAG = 'encryption_tag';
    case IS_READ = 'is_read';
    case CREATED_AT = 'created_at';
}
