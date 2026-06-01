<?php
declare(strict_types=1);

namespace App\Model\DTO;

class UserSaveDTO{
    public function __construct(
        public string $username,
        public string $passwordHash,
        public string $authtoken,
        public string $role,
        public \DateTime $lastLogin,
        public string $email
    ) {}
}