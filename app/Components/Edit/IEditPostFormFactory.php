<?php
declare(strict_types=1);

namespace App\Components;

interface IEditPostFormFactory
{
    public function create(): EditPostForm;
}