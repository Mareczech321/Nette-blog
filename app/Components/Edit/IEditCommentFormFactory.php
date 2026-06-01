<?php

declare(strict_types=1);

namespace App\Components;

interface IEditCommentFormFactory{
    public function create(): EditCommentForm;
}