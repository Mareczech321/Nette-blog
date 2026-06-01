<?php
declare(strict_types=1);

namespace App\Model\DTO;

class SignUpDTO{
    public function __construct(
        public string $username,
        public string $password,
        public string $email
    ) {}
}