<?php
declare(strict_types=1);

namespace App\Components;

use App\Model\UserFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class ProfileEditForm extends Control
{
    public function __construct(
        private UserFacade $userFacade
    ) {}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/ProfileEditForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentProfileForm(): Form
    {
        $form = new Form;

        $presenter = $this->getPresenter();
        $userId = $presenter->getUser()->isLoggedIn() ? (int) $presenter->getUser()->getId() : 0;

        $form->addText('name', 'Celé jméno:')
            ->setRequired('Zadejte prosím své jméno.')
            ->addRule(function ($control) use ($userId) {
                $existingUser = $this->userFacade->getUserByName($control->getValue());

                return !$existingUser || $existingUser->id === $userId;
            }, 'Toto jméno je již obsazené jiným uživatelem. Zvolte si prosím jiné.');

        $form->addEmail('email', 'E-mailová adresa:')
            ->setRequired('Zadejte prosím platný e-mail.')
            ->addRule(function ($control) use ($userId) {
                $existingUser = $this->userFacade->getUserByEmail($control->getValue());

                return !$existingUser || $existingUser->id === $userId;
            }, 'Tento e-mail je již zaregistrovaný jiným uživatelem.');

        $form->addSubmit('save', 'Uložit nastavení profilu');

        $presenter = $this->getPresenter();
        if ($presenter->getUser()->isLoggedIn()) {
            $userId = (int) $presenter->getUser()->getId();
            $userDb = $this->userFacade->getUserByID($userId);

            if ($userDb) {
                $form->setDefaults([
                    'name' => $userDb->username,
                    'email' => $userDb->email,
                ]);
            }
        }

        $form->onSuccess[] = function (Form $form, $data): void {
            /** @var \stdClass $data */
            $this->profileFormSucceeded($data);
        };

        return $form;
    }

    public function profileFormSucceeded(\stdClass $data): void
    {
        $presenter = $this->getPresenter();
        $user = $presenter->getUser();

        if (!$user->isLoggedIn()) {
            $presenter->redirect('Sign:in');
        }

        $userId = (int) $user->getId();

        $this->userFacade->updateProfile($userId, $data->name, $data->email);

        $identity = $user->getIdentity();

        if ($identity instanceof \Nette\Security\SimpleIdentity) {

            $identityData = $identity->getData();

            $identityData['username'] = $data->name;
            $identityData['email'] = $data->email;

            $newIdentity = new \Nette\Security\SimpleIdentity(
                $identity->getId(),
                $identity->getRoles(),
                $identityData
            );

            $user->login($newIdentity);
        }

        $presenter->flashMessage('Profil byl úspěšně aktualizován.', 'success');
        $presenter->redirect('this');
    }
}