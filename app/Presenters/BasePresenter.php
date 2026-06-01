<?php
declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\Session\MultiAccountSession;
use App\Model\MessageFacade;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    #[\Nette\DI\Attributes\Inject]
    public MultiAccountSession $multiAccountSession;

    /** @inject */
    public MessageFacade $messageFacade;

    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->activeAccounts = $this->multiAccountSession->getIdentities();
        if ($this->getUser()->isLoggedIn()) {
            $this->template->unreadMessagesCount = $this->messageFacade->getUnreadCount(
                (int)$this->getUser()->getId()
            );
        }
    }

    public function handleSwitchAccount(int $targetId): void
    {
        $identity = $this->multiAccountSession->getIdentity($targetId);
        if ($identity !== null) {
            $this->getUser()->login($identity);
            $this->flashMessage('Účet byl úspěšně přepnut.', 'success');
        } else {
            $this->flashMessage('K tomuto účtu nejste přihlášeni.', 'error');
        }
        $this->redirect('this');
    }

    public function handleLogoutAll(): void
    {
        $this->getUser()->logout(true);
        $this->multiAccountSession->clear();

        $this->flashMessage('Byli jste odhlášeni ze všech účtů.', 'info');
        $this->redirect('Sign:in');
    }

    public function handleRemoveAccount(int $accountId): void
    {
        $this->multiAccountSession->removeIdentity($accountId);

        if ($this->getUser()->isLoggedIn() && $this->getUser()->getId() === $accountId) {

            $remainingAccounts = $this->multiAccountSession->getIdentities();

            if (count($remainingAccounts) > 0) {
                $firstAvailable = reset($remainingAccounts);
                $this->getUser()->login($firstAvailable);
                $this->flashMessage('Byli jste odhlášeni. Přepnuto na účet ' . ($firstAvailable->getData()['name'] ?? 'Neznámý'), 'info');
            } else {
                $this->getUser()->logout(true);
                $this->flashMessage('Byli jste odhlášeni.', 'info');
                $this->redirect('Sign:in');
            }

        } else {
            $this->flashMessage('Účet byl odebrán ze seznamu.', 'info');
        }

        $this->redirect('this');
    }
}