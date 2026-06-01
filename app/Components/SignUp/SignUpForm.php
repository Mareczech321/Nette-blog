<?php
declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use App\Model\UserFacade;
use App\Model\DTO\SignUpDTO;
use App\Model\Session\MultiAccountSession;

class SignUpForm extends Control
{
    public function __construct(
        private UserFacade $userFacade,
        private MultiAccountSession $multiAccountSession
    ) {}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/SignUpForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentSignUpForm(): Form
    {
        $form = new Form;

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím zadejte své uživatelské jméno.');

        $form->addEmail('email', 'E-mail')
            ->setRequired('Prosím zadejte e-mail.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte své heslo.')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 8);

        $form->addPassword('password_again', 'Heslo znovu:')
            ->setRequired('Prosím zadejte heslo pro kontrolu.')
            ->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $form['password']);

        $form->addCheckbox('login_after', 'Přihlásit se ihned po registraci');

        $form->addSubmit('send', 'Registrovat se');

        $form->onSuccess[] = function (Form $form, $data): void {
            /** @var \stdClass $data */
            $arrayData = (array) $data;

            $username = isset($arrayData['username']) && is_scalar($arrayData['username']) ? (string) $arrayData['username'] : '';
            $password = isset($arrayData['password']) && is_scalar($arrayData['password']) ? (string) $arrayData['password'] : '';
            $email = isset($arrayData['email']) && is_scalar($arrayData['email']) ? (string) $arrayData['email'] : '';

            if ($this->userFacade->getUserByName($username) !== null) {
                /** @var \Nette\Forms\Controls\TextInput $usernameInput */
                $usernameInput = $form['username'];
                $usernameInput->addError('Toto uživatelské jméno je již obsazené. Vyberte si prosím jiné.');
                return;
            }

            if ($this->userFacade->getUserByEmail($email) !== null) {
                /** @var \Nette\Forms\Controls\TextInput $emailInput */
                $emailInput = $form['email'];
                $emailInput->addError('Tento e-mail už je zaregistrovaný! Použijte prosím jiný.');
                return;
            }

            $dto = new SignUpDTO(
                isset($arrayData['username']) && is_scalar($arrayData['username']) ? (string) $arrayData['username'] : '',
                isset($arrayData['password']) && is_scalar($arrayData['password']) ? (string) $arrayData['password'] : '',
                isset($arrayData['email']) && is_scalar($arrayData['email']) ? (string) $arrayData['email'] : ''
            );

            $this->userFacade->registerUser($dto);

            $presenter = $this->getPresenter();

            if (!empty($arrayData['login_after'])) {
                try {
                    $presenter = $this->getPresenter();
                    $presenter->getUser()->login($username, $password);

                    $identity = $presenter->getUser()->getIdentity();
                    if ($identity !== null) {
                        $this->multiAccountSession->addIdentity($identity);
                    }

                    $presenter->flashMessage('Registrace byla úspěšná a byli jste automaticky přihlášen.', 'success');
                    $presenter->redirect('Home:');
                } catch (\Nette\Security\AuthenticationException $e) {
                    throw $e;
                }
            }

            $presenter->flashMessage('Registrace byla úspěšná. Nyní se můžete přihlásit.', 'success');
            $presenter->redirect('Sign:in');
        };

        return $form;
    }
}