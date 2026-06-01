<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Components\CreateForm;
use App\Components\EditPostForm;
use App\Model\PostFacade;
use Nette;
use App\Components\ICreateFormFactory;
use App\Components\IEditPostFormFactory;

class EditPresenter extends BasePresenter
{
    /** @var int|null */
    private ?int $commentId = null;
    private ?int $postId = null;

    public function __construct(
        private PostFacade $postFacade,
        private ICreateFormFactory $createFormFactory,
        private IEditPostFormFactory $editPostFormFactory
    ) {
        parent::__construct();
    }

    public function actionCreate(): void{
        if (!($this->getUser()->isLoggedIn())){
            $this->flashMessage('Nejste přihlášeni!');
            $this->redirect('Home:');
        }
    }

    public function createComponentPostForm(): CreateForm
    {
        return $this->createFormFactory->create();
    }

    public function createComponentEditPostForm(): EditPostForm
    {
        return $this->editPostFormFactory->create();
    }

    public function actionEdit(?int $id = null, ?int $commentId = null): void
    {
        if ($commentId !== null) {
            $this->commentId = $commentId;
            $comment = $this->postFacade->getComment($commentId);

            if (!$comment) {
                $this->error('Komentář neexistuje.');
            }

            if (!$this->getUser()->isInRole('admin')) {
                $this->error('Nemáte oprávnění upravovat komentáře.', Nette\Http\IResponse::S403_FORBIDDEN);
            }
            return;
        }

        if ($id !== null) {
            $post = $this->postFacade->getPost($id);
            if (!$post) { $this->error('Příspěvek neexistuje.'); }
        }
    }

    public function renderEdit(?int $id = null, ?int $commentId = null): void
    {
        $this->template->isCommentEdit = ($commentId !== null);
    }

    protected function createComponentEditCommentAdminForm(): Nette\Application\UI\Form
    {
        $form = new Nette\Application\UI\Form;

        $form->addTextArea('content', 'Obsah komentáře:')
            ->setRequired('Komentář nesmí být prázdný.');

        $form->addSubmit('save', 'Uložit změny');

        if ($this->commentId !== null) {
            $comment = $this->postFacade->getComment($this->commentId);
            if ($comment) {
                $form->setDefaults([
                    'content' => $comment->content,
                ]);
            }
        }

        $form->onSuccess[] = function (Nette\Application\UI\Form $form, $data): void {
            if ($this->commentId === null) {
                $this->error();
            }

            $comment = $this->postFacade->getComment($this->commentId);
            if ($comment) {
                $comment->update([
                    'content' => $data->content,
                ]);
                $this->flashMessage('Komentář byl úspěšně upraven.', 'success');
            }

            $this->redirect('Post:show', $this->postId);
        };

        return $form;
    }

    public function actionComment(int $commentId): void
    {
        $comment = $this->postFacade->getComment($commentId);

        if (!$comment) {
            $this->error('Komentář neexistuje.');
        }

        if (!is_numeric($comment->post_id)) {
            $this->error('Neplatné ID příspěvku.');
        }

        $this->postId = (int) $comment->post_id;
        $this->commentId = $commentId;

        if ($this->getUser()->getId() !== $comment->user_id && !$this->getUser()->isInRole('admin')) {
            $this->error('Nemáte oprávnění upravovat komentáře.', Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }

    public function renderComment(int $commentId): void
    {
        $this->template->postId = $this->postId;
    }
}