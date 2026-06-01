<?php

    declare(strict_types=1);

    namespace App\Components;

    interface ICreateFormFactory{
        public function create(): CreateForm;
    }