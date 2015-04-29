<?php
/**
 * Tento soubor je součástí projektu ErNISTo z předmetu A6N33LI Lékařská informatika
 * v 1. ročníku magisterského studia oboru Biomedicínského inženýrství     
 */

use Nette\Application\UI,
    Nette\Security as NS;
/**
 * Presenter, který zajišťije přihlášení a odhlášení uživatele. Založen na sandboxu dodávaném v Nette frameworku 
 */
class SignPresenter extends Nette\Application\UI\Presenter 
{
	/** @persistent */
	public $backlink = '';

        /**
	 * Továrnička pro tvorbu přihlašovacího formuláře 
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('username', 'Přihlašovací jméno',30)
			->setRequired('Vyplňte přihlašovací jméno');

		$form->addPassword('password', 'Heslo',30)
			->setRequired('Vyplňte heslo');

		$form->addCheckbox('remember', 'Zapamatovat si mě na tomto počítači');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}


        /**
         * Zpracuje hodnoty z formuláře a pokusí se přihlásit uživatele
         * @param   Nette\Application\UI\Form     odeslaný formulář
         * @return  void
         */
	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			if ($values->remember) {
				$this->getUser()->setExpiration('+ 14 days', FALSE);
			} else {
				$this->getUser()->setExpiration('+ 20 minutes', TRUE);
			}
			$this->getUser()->login($values->username, $values->password);
			$this->application->restoreRequest($this->backlink);
                        $this->redirect('Cekarna:view');

		} catch (NS\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

        /**
         * Odhlásí uživatele a přesměruje ho na stránku přihlášení
         * @return  void
         */
	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen');
		$this->redirect('in');
	}

}