<?php
declare(strict_types=1);

namespace App\Model\Enum;

enum UserColumns: string
{
    case TABLE_NAME = 'users';
    case USERNAME = 'username';
    case PASSWORD = 'password';
    case AUTH_TOKEN = 'authtoken';
    case ROLE = 'role';
    case EMAIL = 'email';
}
