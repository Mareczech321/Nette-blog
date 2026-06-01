<?php
declare(strict_types=1);

namespace App\Model\Session;

use Nette\Security\IIdentity;

class MultiAccountSession extends BaseSession
{
    private const SECTION_NAME = 'multi_account';

    protected function getSectionName(): string
    {
        return self::SECTION_NAME;
    }

    public function addIdentity(IIdentity $identity): void
    {
        $identities = $this->getIdentities();

        $identities[$identity->getId()] = $identity;

        $this->section->identities = $identities;
    }

    /**
     * @return array<int|string, IIdentity>
     */
    public function getIdentities(): array
    {
        $identities = $this->section->identities;

        if (is_array($identities)) {
            /** @var array<int|string, \Nette\Security\IIdentity> $identities */
            return $identities;
        }

        return [];
    }

    public function getIdentity(int|string $id): ?IIdentity
    {
        $identities = $this->getIdentities();
        return $identities[$id] ?? null;
    }

    public function removeIdentity(int|string $id): void
    {
        $identities = $this->getIdentities();

        if (isset($identities[$id])) {
            unset($identities[$id]);
            $this->section->identities = $identities;
        }
    }
}