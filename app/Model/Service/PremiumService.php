<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Security\User;

final class PremiumService
{
    public function __construct(private UserFacade $userFacade) {}

    public function isUserPremium(User $user): bool
    {
        if (!$user->isLoggedIn()) return false;
        if ($user->isInRole('admin')) return true;

        $userId = $user->getId();
        if ($userId === null) {
            return false;
        }

        $dbUser = $this->userFacade->getUserByID((int)$userId);
        if ($dbUser && $dbUser->is_premium && !empty($dbUser->premium_duration)) {
            $duration = $dbUser->premium_duration;

            if (is_string($duration)) {
                return new \DateTime($duration) > new \DateTime();
            }
        }
        return false;
    }
}