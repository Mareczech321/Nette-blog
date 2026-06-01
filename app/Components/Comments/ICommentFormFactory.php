<?php
    declare(strict_types=1);

    namespace App\Components;

    interface ICommentFormFactory
    {
        public function create(int $postId): CommentForm;
    }