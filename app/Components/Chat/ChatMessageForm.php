<?php


declare(strict_types=1);

namespace App\Components;

use App\Model\MessageFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use App\Model\Mapper\MessageMapper;

class ChatMessageForm extends Control
{
    public function __construct(
        private MessageFacade $messageFacade,
        private MessageMapper $messageMapper
    ){}

    public function render(): void
    {
        $this->getTemplate()->setFile(__DIR__ . '/ChatMessageForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentForm(): Form
    {
        $form = new Form;
        $presenter = $this->getPresenter();

        $form->addText('content', 'Zpráva:')->setRequired('Napište něco.');
        $form->addSubmit('send', 'Odeslat');

        $form->onSuccess[] = function (Form $form): void {
            $this->formSucceeded($form);
        };

        return $form;
    }

    public function formSucceeded(Form $form): void
    {
        /** @var \stdClass $values */
        $values = $form->getValues();

        $presenter = $this->getPresenter();

        assert($presenter instanceof \App\Presenters\ChatPresenter);

        $messageDto = $this->messageMapper->toDTO(
            (int)$presenter->getUser()->getId(),
            $presenter->targetUserId,
            $values
        );

        $this->messageFacade->sendMessage($messageDto);

        $form->setValues(['content' => ''], true);

        if ($presenter->isAjax()) {
            $presenter->redrawControl('chatMessages');
            $presenter->redrawControl('chatFormSnippet');
        } else {
            $presenter->redirect('this');
        }
    }
}