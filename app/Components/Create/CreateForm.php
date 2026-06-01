<?php
declare(strict_types=1);

namespace App\Components;

use App\Model\PostFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use App\Model\Mapper\PostFormMapper;
use App\Model\ImageManager;
use App\Model\UserFacade;
class CreateForm extends Control
{
    public function __construct(
        private PostFacade $postFacade,
        private PostFormMapper $postFormMapper,
        private ImageManager $imageManager,
        private UserFacade $userFacade
    ) {}

    public function render(): void
    {
        $presenter = $this->getPresenter();
        $isPremium = false;
        $userId = (int) $presenter->getUser()->getId();

        if ($presenter->getUser()->isLoggedIn()) {
            $user = $this->userFacade->getUserByID($userId);
            $isPremium = $user ? $user->is_premium : false;
        }

        $this->getTemplate()->isUserPremium = $isPremium;
        $this->getTemplate()->setFile(__DIR__ . '/CreateForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentPostForm(): Form
    {
        $form = new Form;

        $form->addText('title', 'Titulková řádka:')
            ->setRequired('Zadejte prosím titulek příspěvku.');

        $form->addTextArea('content', 'Obsah příspěvku:')
            ->setRequired('Napište prosím obsah příspěvku.');

        $form->addUpload('image', 'Nahrát úvodní fotografii:')
            ->addRule(Form::IMAGE, 'Uživatelský obrázek musí být JPEG, PNG, GIF nebo WebP.');

        $form->addCheckbox('is_premium', '👑 Prémiový článek (pouze pro předplatitele)');

        $form->addSubmit('send', 'Uložit a publikovat');

        $form->onSuccess[] = function (Form $form, $data): void {
            /** @var \stdClass $data */
            $this->postFormSucceeded($data);
        };

        return $form;
    }

    public function postFormSucceeded(\stdClass $data): void
    {
        $presenter = $this->getPresenter();
        $postId = $presenter->getParameter('id');
        $id = ($postId !== null && is_scalar($postId)) ? (int) $postId : null;

        /** @var array<string, mixed> $arrayData */
        $arrayData = (array) $data;

        /** @var \Nette\Http\FileUpload $file */
        $file = $arrayData['image'];
        $imagePathForDb = null;

        if ($file->isOk() && $file->isImage()) {
            $imagePathForDb = $this->imageManager->saveFromUpload($file);
        }

        $presenter = $this->getPresenter();
        $userId = $presenter->getUser()->isLoggedIn() ? (int) $presenter->getUser()->getId() : null;

        $dto = $this->postFormMapper->mapFormToDTO((array)$data, 0, $imagePathForDb, $userId);

        $post = $this->postFacade->savePost($id, $dto);

        $presenter->flashMessage($id !== null ? 'Příspěvek byl upraven.' : 'Příspěvek byl publikován.', 'success');
        $presenter->redirect('Post:show', $id ?? $post->id);
    }
}