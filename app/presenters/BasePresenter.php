<?php
/**
 * Tento soubor je součástí projektu ErNISTo z předmetu A6N33LI Lékařská informatika
 * v 1. ročníku magisterského studia oboru Biomedicínského inženýrství     
 */
use Nette\Forms\Form,    
    Nette\Diagnostics\Debugger;
/**
 * Základní presenter (Controler), ze kterého dědí všechny ostatní presentery. Obsahuje předevší metrody pro tvorbu 
 * a ovládání formulářů pro pacienta a návštěvu, které se vyskytují ve více částech aplikace.
 * 
 * @author     Jakub Mottl
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
    * Při startu ověř, zda je uživatel přihlášený a když není, ulož adresu a přesměruj ho na přihlášení. 
    * @return    void
    */  
    protected function startup(){
        parent::startup();
        
        setlocale(LC_ALL,'cs_CZ.utf8');
        
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Sign:in', array('backlink' => $this->storeRequest()));
	}
    }

    /**
    * Vykreslí formulář pro přidání návštevy, pokud je předán parametr ID pacienta, bude vybrán defaulutně 
    * @param     int   ID pacienta
    * @return    void
    */  
     public function handleAddNavsteva($id = null) {        
        if ($this->presenter->isAjax()) {    
            $this->template->showNavstevaForm = true;
            if (!is_null($id)) {
                $form = $this['navstevaForm'];
                
                $pacient = $this->getService('model')->getPacient($id);
                if (!$pacient) $this->error('Pacient nenalezen');
                                
                $form['pacient_name']->setDefaultValue($pacient->prijmeni.' '.$pacient->jmeno);
                $form['pacient_id']->setDefaultValue($pacient->id);                
            }
            
            $this->invalidateControl('navstevaForm');
        }
      }
    /**
    * Vykreslí formulář pro editaci návštevy a naplní ho hodnotami
    * @param     int   ID návštěvy
    * @return    void
    */      
     public function handleEditNavsteva($id) {                
       if ($this->presenter->isAjax()) {
            $this->template->showNavstevaForm = true;
            $form = $this['navstevaForm'];
                $row = $this->getService('model')->getNavsteva($id);
                $row->pacient_name = $row->pacient->prijmeni .' '. $row->pacient->jmeno;
                
                $row->cas = $row->datum->format('H:i');
                $row->datum = $row->datum->format('d.m.Y');                
                if (!$row) $this->error('Návštěva nenalezena');

            $form->setDefaults($row);
            $this->invalidateControl('navstevaForm');
        }
      }
    
    /**
    * Vykreslí formulář pro přidání pacienta
    * @return    void
    */      
     public function handleAddPacient() {        
        if ($this->presenter->isAjax()) {    
            $this->template->showPacientForm = true;
            $this->invalidateControl('pacientForm');
        }
     }
    
     /**
    * Vykreslí formulář pro editaci pacienta a naplní ho hodnotami
    * @param     int   ID pacienta
    * @return    void
    */      
     public function handleEditPacient($id) {                
        if ($this->presenter->isAjax()) {
            $this->template->showPacientForm = true;
            $form = $this['pacientForm'];
            $form['id']->setAttribute('class', 'gray');
            $form['id']->setAttribute('readonly', 'readonly');
            $form['new']->setDefaultValue(1);
                    
            if (!$form->isSubmitted()) {
                $row = $this->getService('model')->getPacient($id);
                if (!$row) $this->error('Pacient nenalezen');
            }
            $form->setDefaults($row);
            $this->invalidateControl('pacientForm');
        }
      }
    
      
      /**
    * Továrnička pro tvorbu návštěvního formuláře 
    * @param string     název komponenty
    * @return Nette\Application\UI\Form
    */
        protected function createComponentNavstevaForm($name) {
            $form = new  Nette\Application\UI\Form;
            
            $datum = new DateTime;
        
            $form->addHidden('id');
            $form->addText('datum', 'Datum',30)
                   ->setRequired('Vyplňte datum')
                   ->setAttribute('type','date')
                   ->setAttribute('readonly', 'readonly')
                   ->setDefaultValue($datum->format('d.m.Y'));
            $form->addText('cas', 'Čas',5)
                   ->addRule(Form::LENGTH, 'Čas musí mít právě %d znaků',5)
                   ->addRule(Form::PATTERN, 'Čas musí být ve formátu HH:MM', '[0-2]{1}\d{1}:[0-5]{1}\d{1}')
                   ->setRequired('Vyplňte čas')
                   ->setAttribute('type','time')
                   ->setDefaultValue($datum->format('H:i'));
            $form->addText('pacient_name', 'Pacient',30)
                    ->setRequired('Vyberte pacienta')
                    ->setAttribute('placeholder', 'napište alespoň 3 písmena');;
            $form->addHidden('pacient_id')
                    ->setRequired('Vyberte pacineta ze seznamu');
            
            $form->addSelect('pracoviste_id', 'Pracoviště',
                    $this->getService('model')->getSelectArray('pracoviste')
                    );
                    
            $form->addSelect('oddeleni_id', 'Oddělení',
                    $this->getService('model')->getSelectArray('oddeleni')
                    );
            
            $form->addText('cislo_mistnosti', 'Číslo místnosti')
                    ->setRequired('Vyplňte číslo místnoti')
                    ->addRule(Form::INTEGER,'Číslo místnosti musí být číslo')
                    ->addRule(Form::RANGE, 'Číslo místnosti musí být od %d do %d', array(1, 999));

            $form['oddeleni_id']->setDefaultValue('chi');
            $form['pracoviste_id']->setDefaultValue('00829838000');
                    
            $form->addSubmit('submitButton', 'Uložit');
            $form->onSuccess[] = callback($this, 'submitNavstevaForm');
            
            return $form;
        }
        
        /**
         * Zpracuje hodnoty z formuláře a vytvoří novou návštěvu nebo aktualizuje stávající
         * @param   Nette\Application\UI\Form     odeslaný formulář
         * @return  void
         */
        public function submitNavstevaForm($form) {
            $values = $form->getValues();            
            $id = (int) $values['id'];
            
            unset($values['pacient_name']);
            unset($values['id']);
            
            $datum = new DateTime($values['datum'].' '.$values['cas']);
            unset($values['cas']);
            $values['datum'] = $datum;
            
            if ($id > 0) {
                $this->getService('model')->getNavsteva($id)->update($values);
		$this->flashMessage('Návštěva byla aktualizována.');
            } else {
                $this->getService('model')->addNavsteva($values);
		$this->flashMessage('Návštěva byla vytvořena.');
            }
            
            if (isset($this->params['id'])) $this->redirect($this->backlink(),$this->params['id']);
                else $this->redirect($this->backlink());
        }
        
        /**
        * Továrnička pro tvorbu pacienského formuláře 
        * @param string     název komponenty
        * @return Nette\Application\UI\Form
        */
        protected function createComponentPacientForm($name) {
            $form = new  Nette\Application\UI\Form;
                   
            $form->addHidden('new', 0);
            
            $form->addGroup('Osobní údaje');
            
            $form->addText('id', 'Rodné číslo',10)                   
                ->setRequired('Vyplňte rodné číslo')
                ->addRule(Form::INTEGER, 'Rodné číslo musí být číslo')
                ->addRule(Form::LENGTH, 'Rodné číslo musí mít přesně %d znaků', 10)
                ->setAttribute('type','number');
            
            $form->addText('titul_pred', 'Titul před jménem',30);
            $form->addText('jmeno', 'Jméno',30)
                    ->setRequired('Vyplňte jméno pacienta');
            $form->addText('prijmeni', 'Příjmení',30)
                    ->setRequired('Vyplňte příjmení pacienta');
            $form->addText('titul_za', 'Titul za jménem',30);
                    
            $form->addRadioList('pohlavi', 'Pohlaví', array('muž','žena'))
                ->setDefaultValue(0)
                ->getSeparatorPrototype()->setName(NULL);
            
            $form->addSelect('pojistovna_id', 'Pojišťovna',
                    $this->getService('model')->getSelectArray('pojistovna'))
                    ->setDefaultValue('111')
                    ->setAttribute('data-placeholder','Vyberte pojišťovnu');
            
            
            $form->addSelect('rodiny_stav_id', 'Rodinny stav',
                    $this->getService('model')->getSelectArray('rodiny_stav'))
                    ->setDefaultValue(1)
                    ->setAttribute('data-placeholder','Vyberte rodinný stav');
            
            $form->addGroup('Adresa');
            
            $form->addText('ulice', 'Ulice',30)
                ->setRequired('Vyplňte název ulice');
            
            $form->addText('cp', 'Číslo popisné',30)
                ->addRule(Form::INTEGER, 'Číslo popisné musí být číslo')
                ->addRule(Form::RANGE, 'Číslo popisné musí být od %d do %d', array(1, 10000))
                ->setAttribute('type','number')    
                ->setRequired('Vyplňte číslo popisné');
            
            $form->addText('mesto', 'Město',30)
                ->setRequired('Vyplňte název obce');
            
            $form->addText('psc', 'PSČ',30)
                ->addRule(Form::INTEGER, 'PSČ musí být číslo')
                ->addRule(Form::LENGTH, 'PSČ musí mít přesně %d znaků', 5)
                ->addRule(Form::RANGE, 'PSČ musí být od %d do %d', array(10000, 99999))
                ->setAttribute('type','number')    
                ->setRequired('Vyplňte poštovní směrovací číslo');
            
            $form->setCurrentGroup(NULL);
            
            $form->addSubmit('submitButton', 'Uložit');
            $form->onSuccess[] = callback($this, 'submitPacientForm');
            
            return $form;
        }
    
        /**
         * Zpracuje hodnoty z formuláře a vytvoří nového pacienta nebo aktualizuje stávajícího
         * @param   Nette\Application\UI\Form     odeslaný formulář
         * @return  void
         */
        public function submitPacientForm($form) {
            $values = $form->getValues();            
            $new = $values['new'];
            unset($values['new']);
            
            if ($new > 0) {
                $id = $values['id'];
                unset($values['id']);
                $this->getService('model')->getPacient($id)->update($values);
		$this->flashMessage('Pacient byl aktualizován.');
            } else {
                $this->getService('model')->addPacient($values);
		$this->flashMessage('Pacient byl přidán.');
            }            
            
            if (isset($this->params['id'])) $this->redirect($this->backlink(),$this->params['id']);
                else $this->redirect($this->backlink());
        }

        /**
         * Smaže návštěvu, vkyreslí zprávu a přesměruje na čekárnu.
         * @param   int     ID návštevy
         * @return  void
         */        
        public function actionDeleteNavsteva($id) {
          $this->getService('model')->getNavsteva($id)->delete();
          $this->flashMessage('Návštěva byla smazána');
          $this->redirect('Cekarna:view');
        }
}
