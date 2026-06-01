<?php
declare(strict_types=1);

namespace App\Components;

interface ISearchFormFactory
{
    public function create(): SearchForm;
}