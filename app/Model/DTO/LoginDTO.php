<?php


    declare(strict_types=1);

    namespace App\Model\DTO;

    class LoginDTO{
        public function __construct(
            public string $username,
            public string $password,
        ){}
    }