<?php
declare(strict_types=1);

namespace App\Model\Session;

class CartSession extends BaseSession
{
    private const SECTION_NAME = 'premium_cart';
    protected function getSectionName(): string
    {
        return self::SECTION_NAME;
    }

    public function setPlan(string $planId): void
    {
        $this->section->plan = $planId;
    }

    public function getPlan(): mixed
    {
        return $this->section->plan ?? null;
    }

    public function removePlan(): void
    {
        unset($this->section->plan);
    }

    public function setSuccess(bool $state): void
    {
        $this->section->isSuccess = $state;
    }

    public function isSuccess(): bool
    {
        return (bool) ($this->section->isSuccess ?? false);
    }

    public function removeSuccess(): void
    {
        unset($this->section->isSuccess);
    }
}