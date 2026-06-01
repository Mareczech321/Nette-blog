<?php
declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use App\Model\PostFacade;
use App\Model\Mapper\CommentFormMapper;

class CommentForm extends Control
{
    public function __construct(
        private int $postId,
        private PostFacade $postFacade,
        private CommentFormMapper $commentFormMapper
    ) {}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/CommentForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentForm(): Form
    {
        $form = new Form;
        $presenter = $this->getPresenter();

        $replyTo = $presenter->getParameter('replyTo');
        $form->addHidden('parent_id', $replyTo);

        $nameInput = $form->addText('name', 'Jméno:')
            ->setRequired('Zadejte prosím jméno');

        $presenter = $this->getPresenter();
        if ($presenter->getUser()->isLoggedIn()) {
            /** @var \Nette\Security\SimpleIdentity|null $identity */
            $identity = $presenter->getUser()->getIdentity();

            $username = $identity !== null && is_string($identity->name) ? $identity->name : '';

            if ($username !== '') {
                $nameInput->setDefaultValue($username)
                    ->setHtmlAttribute('readonly', true);
            }
        }

        $emailInput = $form->addEmail('email', 'Email:');

        if ($presenter->getUser()->isLoggedIn()){
            /** @var \Nette\Security\SimpleIdentity|null $identity */
            $identity = $presenter->getUser()->getIdentity();

            $email = $identity !== null && is_string($identity->email) ? $identity->email : '';

            if ($email !== '') {
                $emailInput->setDefaultValue($email)
                    ->setHtmlAttribute('readonly', true);
            }
        }

        $form->addTextArea('content', 'Komentář:')->setRequired('Napište prosím text komentáře');
        $form->addSubmit('send', 'Publikovat komentář');

        $form->onSuccess[] = function (Form $form, $data): void {
            /** @var \stdClass $data */
            $this->formSucceeded($form, $data);
        };

        return $form;
    }

    public function formSucceeded(Form $form, \stdClass $data): void
    {
        $arrayData = (array) $data;
        $presenter = $this->getPresenter();

        $parentId = !empty($arrayData['parent_id']) ? (int) $arrayData['parent_id'] : null;
        $userId = $presenter->getUser()->isLoggedIn() ? (int) $presenter->getUser()->getId() : null;

        $commentDto = $this->commentFormMapper->mapFormToDTO(
            data: $arrayData,
            postId: $this->postId,
            userId: $userId,
            parentId: $parentId
        );

        $this->postFacade->saveComment($commentDto);

        $presenter->flashMessage('Komentář byl úspěšně přidán.', 'success');
        $presenter->redirect('this');
    }
}