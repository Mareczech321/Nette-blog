<?php
declare(strict_types=1);

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class SearchForm extends Control
{
    /** @var array<callable(string $query): void> */
    public array $onSearch = [];

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/SearchForm.latte');
        $this->getTemplate()->render();
    }

    protected function createComponentForm(): Form
    {
        $form = new Form;

        $form->addText('q', 'Hledat:')
            ->setHtmlAttribute('placeholder', 'Hledat články podle titulku...');

        $form->addSubmit('search', 'Hledat');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form): void
    {
        /** @var \stdClass $values */
        $values = $form->getValues();

        $query = (string) ($values->q ?? '');

        $this->onSearch($query);
    }
}