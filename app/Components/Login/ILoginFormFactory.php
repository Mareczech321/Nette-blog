<?php
    declare(strict_types=1);

    namespace App\Components;

    interface ILoginFormFactory{
        public function create(): LoginForm;
    }