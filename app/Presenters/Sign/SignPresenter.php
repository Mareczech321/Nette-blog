<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Components\SignUpForm;
use App\Model\UserFacade;
use Nette;
use Nette\Application\UI\Form;
use App\Components\ILoginFormFactory;
use App\Components\ISignUpFormFactory;

class SignPresenter extends BasePresenter{

    public function __construct(
        private ILoginFormFactory $loginFormFactory,
        private ISignUpFormFactory $signUpFormFactory
    ){}

    protected function createComponentSignInForm(): \App\Components\LoginForm{
        return $this->loginFormFactory->create();
    }

    protected function createComponentSignUpForm(): \App\Components\SignUpForm{
        return $this->signUpFormFactory->create();
    }

    public function actionOut(): void
    {
        $this->getUser()->logout(true);
        $this->flashMessage('Byli jste úspěšně odhlášeni.', 'success');
        $this->redirect('Home:default');
    }
}