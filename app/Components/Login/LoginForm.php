<?php
declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use App\Model\UserFacade;
use App\Model\Session\MultiAccountSession;

class LoginForm extends Control
{
    public function __construct(
        private UserFacade $userFacade,
        private MultiAccountSession $multiAccountSession
    ){}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/LoginForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form;

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím zadejte své uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte své heslo.');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = function (Form $form, $data): void {
            /** @var \stdClass $data */
            $arrayData = (array) $data;

            $dto = new \App\Model\DTO\LoginDTO(
                isset($arrayData['username']) && is_scalar($arrayData['username']) ? (string) $arrayData['username'] : '',
                isset($arrayData['password']) && is_scalar($arrayData['password']) ? (string) $arrayData['password'] : ''
            );

            $presenter = $this->getPresenter();
            $user = $this->userFacade->getUserByName($dto->username);

            try {
                $presenter->getUser()->login($dto->username, $dto->password);
                if ($user !== null) {
                    $user->update([
                        'last_login' => new \DateTime()
                    ]);
                }
                $identity = $presenter->getUser()->getIdentity();
                if ($identity !== null) {
                    $this->multiAccountSession->addIdentity($identity);
                }

                $presenter->flashMessage('Byli jste přihlášeni!', 'success');
                $presenter->redirect('Home:');
            } catch (\Nette\Security\AuthenticationException $e) {
                $presenter->flashMessage('Nesprávné přihlašovací jméno nebo heslo.', 'error');
            }
        };

        return $form;
    }
}