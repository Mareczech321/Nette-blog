<?php
declare(strict_types=1);

namespace App\Model\Session;

use Nette\Http\Session;
use Nette\Http\SessionSection;

abstract class BaseSession
{
    protected SessionSection $section;

    public function __construct(Session $session)
    {
        $this->section = $session->getSection($this->getSectionName());
    }

    abstract protected function getSectionName(): string;

    public function clear(): void
    {
        $this->section->remove();
    }

    public function setExpiration(string $time): void
    {
        $this->section->setExpiration($time);
    }
}