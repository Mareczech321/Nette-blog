<?php
declare(strict_types=1);

namespace App\Model\Auth;

use Nette\Security\Authenticator as NetteAuthenticator;
use Nette\Security\IIdentity as NetteIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Security\Passwords;
use Nette\Database\Explorer;

class Authenticator implements NetteAuthenticator
{
    public function __construct(
        private Explorer $database,
        private Passwords $passwords
    ) {}

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->database->table('users')
            ->where('username', $username)
            ->fetch();

        if (!$row) {
            throw new \Nette\Security\AuthenticationException('Uživatel nebyl znalezen.');
        }

        $dbPassword = $row->offsetGet('password');
        $passwordString = is_scalar($dbPassword) ? (string) $dbPassword : '';

        if (!$this->passwords->verify($password, $passwordString)) {
            throw new \Nette\Security\AuthenticationException('Nesprávné heslo.');
        }

        $dbId = $row->offsetGet('id');
        $userId = is_numeric($dbId) ? (int) $dbId : 0;
        $usernameString = is_scalar($row->offsetGet('username')) ? (string) $row->offsetGet('username') : '';

        $dbRole = $row->offsetGet('role');
        $userRole = is_scalar($dbRole) ? (string) $dbRole : 'user';

        $emailString = is_scalar($row->offsetGet('email')) ? (string) $row->offsetGet('email') : '';
        $dbPremiumDuration = $row->offsetGet('premium_duration');

        return new SimpleIdentity(
            $userId,
            [$userRole],
            [
                'name' => $usernameString,
                'email' => $emailString,
                'premium_duration' => $dbPremiumDuration instanceof \DateTimeInterface ? $dbPremiumDuration->format(\DateTime::ATOM) : null
            ]
        );
    }

    public function sleepIdentity(NetteIdentity $identity): NetteIdentity
    {
        return $identity;
    }

    public function wakeupIdentity(NetteIdentity $identity): ?NetteIdentity
    {
        return $identity;
    }
}