<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\DTO\SignUpDTO;
use App\Model\DTO\UserSaveDTO;
use App\Model\Enum\UserColumns;
use Nette\Security\User;

class UserRepository extends BaseRepository{
    protected function getTableName(): string
    {
        return UserColumns::TABLE_NAME->value;
    }

    /**
     * @param object $dto
     * @return array<string, mixed>
     */
    protected function mapDTOtoArray(object $dto): array
    {
        /** @var UserSaveDTO $dto */
        return [
            UserColumns::USERNAME->value => $dto->username,
            UserColumns::PASSWORD->value => $dto->passwordHash,
            UserColumns::AUTH_TOKEN->value => $dto->authtoken,
            UserColumns::ROLE->value => $dto->role,
            UserColumns::EMAIL->value => $dto->email
        ];
    }

    public function findByUsername(string $username): ?\Nette\Database\Table\ActiveRow
    {
        return $this->database->table($this->getTableName())
            ->where(UserColumns::USERNAME->value, $username)
            ->fetch();
    }

    public function findByEmail(string $email): ?\Nette\Database\Table\ActiveRow
    {
        return $this->database->table($this->getTableName())
            ->where(UserColumns::EMAIL->value, $email)
            ->fetch();
    }

    /**
     * @return array<array{id: int, username: string}>
     */
    public function searchUsers(string $query, int $excludeUserId): array
    {
        $users = $this->database->table('users')
            ->where('id != ?', $excludeUserId)
            ->where('username LIKE ?', '%' . $query . '%')
            ->limit(10)
            ->fetchAll();

        $result = [];
        foreach ($users as $user) {
            $id = is_numeric($user->id) ? (int) $user->id : 0;
            $username = is_string($user->username) ? $user->username : '';

            $result[] = [
                'id' => $id,
                'username' => $username
            ];
        }

        return $result;
    }
}