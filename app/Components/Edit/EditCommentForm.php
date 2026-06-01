<?php
declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use App\Model\PostFacade;
use App\Model\Mapper\CommentFormMapper;

class EditCommentForm extends Control
{
    public function __construct(
        private PostFacade $postFacade,
        private CommentFormMapper $commentFormMapper,
        private int $postId = 0
    ) {}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/EditCommentForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentEditCommentForm(): Multiplier
    {
        return new Multiplier(function (string $commentId) {
            $form = new Form;
            $form->addTextArea('content', 'Upravit text komentáře:')
                ->setRequired();
            $form->addHidden('comment_id', $commentId);
            $form->addSubmit('save', 'Uložit úpravy')
                ->setHtmlAttribute('class', 'btn-primary');

            $form->onSuccess[] = function (Form $form, mixed $data): void {
                /** @var \stdClass $data */
                $presenter = $this->getPresenter();
                $user = $presenter->getUser();

                $dto = $this->commentFormMapper->mapFormToDTO(
                    (array)$data,
                    (int)$data->comment_id,
                    $this->postId,
                    $user->isLoggedIn() ? (int)$user->getId() : null
                );

                $this->postFacade->saveComment($dto);

                $presenter->flashMessage('Komentář byl úspěšně upraven.', 'success');
                $presenter->redirect('Post:show', $this->postId);
            };

            return $form;
        });
    }
}