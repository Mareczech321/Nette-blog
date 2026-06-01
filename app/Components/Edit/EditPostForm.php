<?php
declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use App\Model\PostFacade;
use App\Model\Mapper\PostFormMapper;

class EditPostForm extends Control
{
    public function __construct(
        private PostFacade $postFacade,
        private PostFormMapper $postFormMapper
    ) {}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/EditPostForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentForm(): Form
    {
        $form = new Form;

        $form->addText('title', 'Název článku:')
            ->setRequired('Zadejte prosím název článku.');

        $form->addTextArea('content', 'Obsah článku:')
            ->setRequired('Zadejte prosím obsah.');

        $form->addCheckbox('is_premium', '👑 Prémiový článek (uzamknout pro běžné uživatele)');

        $form->addSubmit('send', 'Uložit článek');

        $postId = $this->getPresenter()->getParameter('id');

        if ($postId !== null && is_scalar($postId)) {
            $post = $this->postFacade->getPost((int) $postId);
            if ($post) {
                $form->setDefaults([
                    'title' => $post->title,
                    'content' => $post->content,
                    'is_premium' => $post->is_premium
                ]);
            }
        }

        $form->onSuccess[] = function (Form $form, $data): void {
            /** @var \stdClass $data */
            $this->processForm($form, $data);
        };

        return $form;
    }

    public function processForm(Form $form, \stdClass $data): void
    {
        $postIdParam = $this->getPresenter()->getParameter('id');
        $postId = 0;
        if ($postIdParam !== null && is_scalar($postIdParam)) {
            $postId = (int) $postIdParam;
        }

        /** @var array<string, mixed> $arrayData */
        $arrayData = (array) $data;

        $dto = $this->postFormMapper->mapFormToDTO($arrayData, $postId);

        if ($postId) {
            $this->postFacade->savePost($postId, $dto);
            $this->getPresenter()->flashMessage('Článek byl úspěšně upraven.', 'success');
        }else {
            $this->postFacade->savePost(null, $dto);
            $this->getPresenter()->flashMessage('Článek byl vytvořen.', 'success');
        }
        $this->getPresenter()->redirect(':Post:show', $postId);
    }
}