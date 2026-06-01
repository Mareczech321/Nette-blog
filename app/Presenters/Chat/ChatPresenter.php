<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Components\ChatMessageForm;
use App\Model\MessageFacade;
use App\Model\UserFacade;
use App\Components\IChatMessageFormFactory;

class ChatPresenter extends BasePresenter
{
    /** @inject */
    public MessageFacade $messageFacade;

    public function __construct(
        private \Nette\Database\Explorer $database,
        private UserFacade $userFacade,
        private IChatMessageFormFactory $chatMessageFormFactory
    ) {}

    public int $targetUserId = 0;

    public function startup(): void
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        if ($this->getUser()->isLoggedIn()) {
            $this->template->unreadMessagesCount = $this->messageFacade->getUnreadCount(
                (int)$this->getUser()->getId()
            );
        }
    }

    public function renderDetail(): void
    {
        $myId = (int)$this->getUser()->getId();

        $this->messageFacade->markAsRead($this->targetUserId, $myId);

        $messages = $this->messageFacade->getMessages(
            $myId,
            $this->targetUserId
        );
        
        $this->template->messages = $messages;
    }

    public function handleRefresh(): void
    {
        if ($this->isAjax()) {
            $this->messageFacade->markAsRead($this->targetUserId, (int)$this->getUser()->getId());

            $this->redrawControl('chatMessages');

            $this->redrawControl('unreadBadgeSnippet');
        }
    }

    public function actionDetail(int $id): void
    {
        $this->targetUserId = $id;
        $targetUser = $this->database->table('users')->get($id);

        if (!$targetUser) {
            $this->error('Uživatel nenalezen');
        }

        $this->template->targetUser = $targetUser;
    }

    protected function createComponentChatMessageForm(): ChatMessageForm
    {
        return $this->chatMessageFormFactory->create();
    }

    public function renderDefault(): void
    {
        $this->template->conversations = $this->messageFacade->getUserConversations(
            (int)$this->getUser()->getId()
        );
    }

    public function actionSearchUsers(string $q = ''): void
    {
        if (mb_strlen($q) < 2) {
            $this->sendJson(['users' => []]);
        }

        $currentUserId = (int) $this->getUser()->getId();

        $users = $this->userFacade->searchUsers($q, $currentUserId);

        $this->sendJson(['users' => $users]);
    }
}