<?php
    declare(strict_types=1);

    namespace App\Model;

    use App\Model\DTO\SignUpDTO;
    use App\Model\DTO\UserSaveDTO;
    use Nette\Security\Passwords;
    use Nette\Utils\Random;

    class UserFacade
    {
        public function __construct(
            private UserRepository $userRepository,
            private Passwords $passwords,
            private PostRepository $postRepository,
            private CommentRepository $commentRepository,
            private PostLikeRepository $postLikeRepository,
            private CommentLikeRepository $commentLikeRepository
        ) {}

        public function registerUser(SignUpDTO $dto): void
        {
            $hash = $this->passwords->hash($dto->password);
            $authToken = Random::generate(40);

            $defaultRole = 'user';

            $saveDto = new UserSaveDTO(
                $dto->username,
                $hash,
                $authToken,
                $defaultRole,
                new \DateTime(),
                $dto->email
            );

            $this->userRepository->save(null, $saveDto);
            $user = $this->getUserByName($dto->username);
            if ($user !== null) {
                $user->update([
                    'last_login' => new \DateTime()
                ]);
            }
        }

        public function getUserByName(string $username): ?\Nette\Database\Table\ActiveRow
        {
            $user = $this->userRepository->findByUsername($username);

            if ($user === null) {
                return null;
            }

            return $user;
        }

        public function getUserByEmail(string $email): ?\Nette\Database\Table\ActiveRow{
            $user = $this->userRepository->findByEmail($email);

            if ($user === null) {
                return null;
            }

            return $user;
        }

        public function getUserByID(int $id): ?\Nette\Database\Table\ActiveRow
        {
            return $this->userRepository->findById($id);
        }

        public function getAllUsers(): \Nette\Database\Table\Selection
        {
            return $this->userRepository->findAll();
        }

        /**
         * @return array<string, mixed>
         */
        public function getUserStats(int $userId): array
        {
            $userPosts = $this->postRepository->findAll()->where('user_id', $userId);
            $userComments = $this->commentRepository->findAll()->where('user_id', $userId);

            $postLikesReceived = $this->postLikeRepository->findAll()->where('post.user_id', $userId)->count('*');
            $commentLikesReceived = $this->commentLikeRepository->findAll()->where('comment.user_id', $userId)->count('*');

            $postLikesGiven = $this->postLikeRepository->findAll()->where('user_id', $userId)->count('*');
            $commentLikesGiven = $this->commentLikeRepository->findAll()->where('user_id', $userId)->count('*');

            return [
                'posts' => count($userPosts),
                'comments' => count($userComments),
                'postLikesReceived' => $postLikesReceived,
                'commentLikesReceived' => $commentLikesReceived,
                'likesGiven' => $postLikesGiven + $commentLikesGiven,
            ];
        }

        public function isPremiumActive(?\Nette\Database\Table\ActiveRow $user): bool
        {
            if (!$user || !$user->premium_duration) {
                return false;
            }

            $now = new \DateTime();
            return $user->premium_duration > $now;
        }

        public function setPremium(int $id, ?\DateTime $length = null): void
        {
            $userRow = $this->getUserByID($id);

            if ($userRow) {
                if ($length !== null) {
                    $userRow->update([
                        'is_premium' => 1,
                        'premium_duration' => $length
                    ]);
                } else {
                    $userRow->update([
                        'is_premium' => 0,
                        'premium_duration' => null
                    ]);
                }
            }
        }

        public function updatePremium(int $userId, string $action): void
        {
            $userRow = $this->getUserByID($userId);
            if (!$userRow) return;

            if ($action === 'revoke') {
                $userRow->update(['is_premium' => 0, 'premium_duration' => null]);
                return;
            }

            $now = new \DateTimeImmutable();

            $rawDuration = $userRow->premium_duration;

            if ($userRow->is_premium && is_string($rawDuration)) {
                $currentDuration = new \DateTimeImmutable($rawDuration);
                $baseDate = ($currentDuration > $now) ? $currentDuration : $now;
            } else {
                $baseDate = $now;
            }

            if ($action === 'add_month') {
                $newExpiryDate = $baseDate->modify('+1 month');
            } elseif ($action === 'add_3_months') {
                $newExpiryDate = $baseDate->modify('+3 months');
            } elseif ($action === 'add_year') {
                $newExpiryDate = $baseDate->modify('+1 year');
            } else {
                $newExpiryDate = $baseDate;
            }

            $userRow->update([
                'is_premium' => 1,
                'premium_duration' => $newExpiryDate->format('Y-m-d H:i:s')
            ]);
        }
        public function updateProfile(int $userId, string $name, string $email): void
        {
            $userRow = $this->getUserByID($userId);

            if (!$userRow) {
                return;
            }

            $createdAt = $userRow->created_at instanceof \DateTime
                ? $userRow->created_at
                : new \DateTime();

            $saveDto = new UserSaveDTO(
                $name,
                is_scalar($userRow->password) ? (string) $userRow->password : '',  // Ochrana 176
                is_scalar($userRow->authtoken) ? (string) $userRow->authtoken : '', // Ochrana 177
                is_scalar($userRow->role) ? (string) $userRow->role : 'user',       // Ochrana 178
                $createdAt,
                $email
            );

            $this->userRepository->save($userId, $saveDto);
        }

        public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
        {
            $userRow = $this->getUserByID($userId);

            $dbPassword = is_scalar($userRow?->password) ? (string) $userRow->password : '';

            if (!$userRow || $dbPassword === '' || !$this->passwords->verify($oldPassword, $dbPassword)) {
                return false;
            }

            $userRow->update([
                'password' => $this->passwords->hash($newPassword)
            ]);

            return true;
        }

        /**
         * @return array<array{id: int, username: string}>
         */
        public function searchUsers(string $query, int $excludeUserId): array
        {
            return $this->userRepository->searchUsers($query, $excludeUserId);
        }
    }