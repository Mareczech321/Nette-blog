<?php
declare(strict_types=1);

namespace App\Components;

use App\Model\UserFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class ChangePasswordForm extends Control
{
    public function __construct(
        private UserFacade $userFacade
    ) {}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/ChangePasswordForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentPasswordForm(): Form
    {
        $form = new Form;

        $form->addPassword('oldPassword', 'Současné heslo:')
            ->setRequired('Zadejte prosím své současné heslo.');

        $form->addPassword('newPassword', 'Nové heslo:')
            ->setRequired('Zadejte prosím nové heslo.')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8);

        $form->addPassword('newPasswordVerify', 'Nové heslo znovu:')
            ->setRequired('Zadejte prosím nové heslo ještě jednou pro kontrolu.')
            ->addRule($form::EQUAL, 'Hesla se neshodují.', $form['newPassword']);

        $form->addSubmit('save', 'Změnit heslo');

        $form->onSuccess[] = function (Form $form, $data): void {
            /** @var \stdClass $data */

            $presenter = $this->getPresenter();
            $user = $presenter->getUser();

            if (!$user->isLoggedIn()) {
                $presenter->redirect('Sign:in');
            }

            $success = $this->userFacade->changePassword((int) $user->getId(), $data->oldPassword, $data->newPassword);

            if ($success) {
                $presenter->flashMessage('Heslo bylo úspěšně změněno.', 'success');
                $presenter->redirect('this');
            } else {
                $form->addError('Současné heslo není správné.');
            }
        };

        return $form;
    }
}