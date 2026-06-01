<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\ProfileEditForm;
use App\Model\CommentRepository;
use App\Model\PostRepository;
use Nette;
use App\Model\UserFacade;
use App\Components\IProfileEditFormFactory;
use App\Components\IChangePasswordFormFactory;

class UserPresenter extends BasePresenter
{
    public function __construct(
        private UserFacade $userFacade,
        private IChangePasswordFormFactory $changePasswordFormFactory,
        private IProfileEditFormFactory $profileEditFormFactory,
        private PostRepository $postRepository,
        private CommentRepository $commentRepository
    ) {
        parent::__construct();
    }

    public function renderProfile(?string $name = null): void
    {
        $currentUser = $this->getUser();

        if ($name === null) {
            if (!$currentUser->isLoggedIn()) {
                $this->flashMessage('Pro zobrazení profilu se musíte přihlásit.', 'error');
                $this->redirect('Home:');
            }
            $profileUser = $this->userFacade->getUserByID((int) $currentUser->getId());
        } else {
            $profileUser = $this->userFacade->getUserByName($name);
        }

        if (!$profileUser) {
            $this->error('Uživatel nebyl nalezen.');
        }

        $profileUserId = is_scalar($profileUser->id) ? (int) $profileUser->id : 0;

        $stats = $this->userFacade->getUserStats($profileUserId);

        $isMe = $currentUser->isLoggedIn() && $currentUser->getId() === $profileUser->id;
        $isAdmin = $currentUser->isInRole('admin');

        $this->template->userPosts = $this->postRepository->findAll()->where('user_id', $currentUser->getId())->order('created_at DESC');
        $this->template->userComments = $this->commentRepository->findAll()->where('user_id', $currentUser->getId())->order('created_at DESC');

        $this->template->profileUser = $profileUser;
        $this->template->isMe = $isMe;
        $this->template->isAdmin = $isAdmin;
        $this->template->stats = $stats;
    }

    public function handleChangeRole(int $userId, string $role): void
    {
        if (!$this->getUser()->isInRole('admin')) {
            $this->payload->error = 'Nemáte oprávnění.';
            $this->sendPayload();
        }

        if ($userId === $this->getUser()->getId() && $role !== 'admin') {
            $this->payload->error = 'Nemůžete si sám sobě odebrat administrátorská práva.';
            $this->sendPayload();
        }

        $userRow = $this->userFacade->getUserByID($userId);
        if ($userRow) {
            $userRow->update(['role' => $role]);
        }

        $this->payload->message = 'Role aktualizována';
        $this->sendPayload();
    }

    public function handleDeleteUser(int $userId): void
    {
        if (!$this->getUser()->isInRole('admin')) {
            $this->error('Nemáte oprávnění k této akci.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }

        if ($userId === $this->getUser()->getId()) {
            $this->flashMessage('Nemůžete smazat svůj vlastní účet z této obrazovky.', 'error');
            $this->redirect('this');
        }

        $userRow = $this->userFacade->getUserByID($userId);
        if ($userRow) {
            $userRow->delete();
            $this->flashMessage('Uživatel byl úspěšně smazán.', 'info');
        }

        $this->redirect('Admin:default');
    }

    public function handleForcePasswordChange(int $userId): void
    {
        if (!$this->getUser()->isInRole('admin')) {
            $this->error('Nemáte oprávnění.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }

        $userRow = $this->userFacade->getUserByID($userId);
        if ($userRow) {

            $this->flashMessage('Výzva ke změně hesla byla (fiktivně) odeslána.', 'success');
        }

        $this->redirect('this');
    }

    protected function createComponentProfileEditForm(): ProfileEditForm
    {
        return $this->profileEditFormFactory->create();
    }

    protected function createComponentChangePasswordForm(): \App\Components\ChangePasswordForm
    {
        return $this->changePasswordFormFactory->create();
    }
}