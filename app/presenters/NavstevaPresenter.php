<?php
/**
 * Tento soubor je součástí projektu ErNISTo z předmetu A6N33LI Lékařská informatika
 * v 1. ročníku magisterského studia oboru Biomedicínského inženýrství     
 */

use Nette\Forms\Form,
    Nette\Diagnostics\Debugger;

/**
 * Presenter Návštěvy má na starosti veškteré činnosti pojené s návštevou, jako je přidávání, editace a odebírání 
 * diagnoz, terapií a zdravotních informací. Také zařizuje přidávání zamětnanců k návštěvě.   
 *
 * @author Jakub Mottl
 */
class NavstevaPresenter extends BasePresenter {

    /**
    * Vykreslí formulář pro přidání diagnozy
    * @return    void
    */  
    public function handleAddDiagnoza() {        
        $this->template->showDiagnozaForm = true;
        if ($this->presenter->isAjax()) {    
            $this->invalidateControl('diagnozaForm');
        }
     }
    
      /**
    * Vykreslí formulář pro editaci diagnozy a naplní ho hodnotami
    * @param     int   ID diagnozy
    * @return    void
    */      
     public function handleEditDiagnoza($id) {        
        if ($this->presenter->isAjax()) {    
            $form = $this['diagnozaForm'];
            if (!$form->isSubmitted()) {
                $row = $this->getService('model')->getDiagnoza($id);
                $row->diagnoza_name = $row->mkn10->nazev;
                if (!$row) $this->error('Záznam nenalezen');
            }
            $this->template->showDiagnozaForm = true;
            $form->setDefaults($row);
            $this->invalidateControl('diagnozaForm');
        }
      }
      
    /**
    * Vykreslí formulář pro přidání terapie
    * @return    void
    */  
     public function handleAddTerapie() {        
        $this->template->showTerapieForm = true;
        if ($this->presenter->isAjax()) {    
            $this->invalidateControl('terapieForm');
        }
     }
    
      /**
    * Vykreslí formulář pro editaci terapie a naplní ho hodnotami
    * @param     int   ID terapie
    * @return    void
    */     
     public function handleEditTerapie($id) {        
        $this->template->showTerapieForm = true;
        if ($this->presenter->isAjax()) {    
            $form = $this['terapieForm'];
            if (!$form->isSubmitted()) {
                $row = $this->getService('model')->getTerapie($id);
                if (!$row) $this->error('Záznam nenalezen');
            }
            $form->setDefaults($row);
            $this->invalidateControl('terapieForm');
        }
      }
          
    /**
    * Vykreslí formulář pro přidání zdravotních informací k návštěvě. K jedné návštěvě může být pouze jedna sada informací
    * takže pokud neexistuje záznam, formulář se nenaplní žádnými hodnotami. 
    * @param    int ID návštěvy 
    * @return   void
    */  
      public function handleAddInfo($id) {        
        $this->template->showInfoForm = true;
        if ($this->presenter->isAjax()) {    
            $form = $this['infoForm'];
            if (!$form->isSubmitted()) {
                $row = $this->getService('model')->getInfoByNavsteva($id);
                if ($row) $form->setDefaults($row);
            }
            $this->invalidateControl('infoForm');
        }
      }
     
    /**
    * Vykreslí formulář pro připojení zaměstnance k aktuální návštěvě.
    * @return   void
    */ 
     public function handleAddZamestnanec() {        
        $this->template->showZamestnanecForm = true;
        if ($this->presenter->isAjax()) {    
            $this->invalidateControl('zamestnanecForm');
        }
      }

    /**
    * Smaže diagnozu, vykreslí zprávu a přesměruje zpět na návštěvu
    * @return   void
    */  
    public function actionDeleteDiagnoza($id,$navsteva_id) {
        $this->getService('model')->getDiagnoza($id)->delete(); 
        $this->flashMessage('Diagnoza byla vymazána');
        $this->redirect('Navsteva:view',$navsteva_id);
    }

    /**
    * Smaže terapii, vykreslí zprávu a přesměruje zpět na návštěvu
    * @return   void
    */  
    public function actionDeleteTerapie($id,$navsteva_id) {
        $this->getService('model')->getTerapie($id)->delete(); 
        $this->flashMessage('Terapie byla vymazána');
        $this->redirect('Navsteva:view',$navsteva_id);
    }

    /**
    * Odebere zaměstnance od návštěvy, vykreslí zprávu a přesměruje zpět na návštěvu
    * @return   void
    */ 
    public function actionDeleteZamestnanec($id,$navsteva_id) {
        $this->getService('model')->deleteZamestnanec($id,$navsteva_id); 
        $this->flashMessage('Zaměstnanec byl odebrát z návštevy');
        $this->redirect('Navsteva:view',$navsteva_id);
    }
    
    /**
    * Vykreslí návštěvu podle ID, pokud není předáno, přesměruje na vyhledávání 
    * @return   void
    */ 
    public function renderView($id) {         
        if (is_null($id)) $this->redirect ('Navsteva:');
        $navsteva = $this->getService('model')->getNavsteva($id);
        $this->template->navsteva = $navsteva;
        $this->template->diagnozy = $navsteva->related('diagnoza');
        $this->template->terapies = $navsteva->related('terapie');
        $this->template->zdrav_info = $navsteva->related('zdrav_info');
        
        $this->template->pacient = $navsteva->pacient;              
        
        $this->template->id = $id;
    }
    
    /**
    * Vykreslí DASTA XML záznam návštěvy podle ID, pokud není předáno, přesměruje na vyhledávání. Pokud k návštěvě není 
    * připojen žádný zaměstnanec, vyhodí chybu a přesměruje zpět na návštěvu.   
    * @return   void
    */ 
    public function renderDasta($id) {
        if (is_null($id)) $this->redirect ('Navsteva:');
        $navsteva = $this->getService('model')->getNavsteva($id);
        $this->template->navsteva = $navsteva;
        $this->template->diagnozy = $navsteva->related('diagnoza');
        $this->template->terapies = $navsteva->related('terapie');
        
        $pacient = $navsteva->pacient;              
        $pacient->date_born = DateTime::createFromFormat('ymd',substr($pacient->id, 0, 6));      
        
        $this->template->pacient = $pacient;
        
        $zamestnanci = $navsteva->related('navsteva_has_zamestnanec')->fetch();
        
        if (!$zamestnanci) {
            $this->flashMessage('Návštěva musí mít zaměstance, aby se dal vytvořit DASTA záznam!','error');
            $this->redirect('Navsteva:view',$id);
        }else {
            $this->template->zamestnanec = $zamestnanci->zamestnanec;
        }
        
    }
    /**
    * Vylhedávání diagnozy. Předá vyhledávaný termín do modelu a vrácené řádky s diagnozou předá do šablony
    * @param    string hledaný termín
    * @return   void
    */ 
    public function actionAutocompleteDiagnoza($term) {
        $this->template->list = $this->getService('model')->getDiagnozaByTerm($term);
    }
    
    /**
    * Vyhledávání zaměstnance. Předá vyvledávaný termín do modelu a vrácené řádky s návštěvami předá do šablony
    * @param    string hledaný termín
    * @return   void
    */ 
    public function actionAutocompleteZamestnanec($term) {
        $this->template->list = $this->getService('model')->getZamestnanecByTerm($term);
    }
    
    /**
    * Vyhledávání návštěvy. Předá vyvledávaný termín do modelu a vrácené řádky s návštěvami předá do šablony
    * @param    string hledaný termín
    * @return   void
    */ 
    public function actionSearchNavsteva($term) {
        $this->template->list = $this->getService('model')->getNavstevaByTerm($term);
    }
    
    /**
    * Továrnička pro tvorbu formuláře diagnozy 
    * @param string     název komponenty
    * @return Nette\Application\UI\Form
    */
    protected function createComponentDiagnozaForm($name) {
            $form = new Nette\Application\UI\Form;

            $form->addHidden('id');
            $form->addHidden('navsteva_id',$this->getParameter('id'));
            $form->addHidden('mkn10_id')
                    ->setRequired('Vyberte prosím diagnozu ze seznamu');
            $form->addText('diagnoza_name', 'Diagnóza',30)
                    ->setRequired('Vyplňte diagnozu')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka je %d znaků', 30)
                    ->setAttribute('placeholder', 'napište alespoň 3 písmena');
            $form->addText('pristroj', 'Přístroj')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka je %d znaků', 45);
            $form->addText('material', 'Materiál')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka je %d znaků', 255);
            $form->addTextArea('komentar', 'Komentář');

            $form->addSubmit('submitButton', 'Uložit');
            $form->onSuccess[] = callback($this, 'submitDiagnozaForm');

            return $form;
        }    
     
    /**
     * Zpracuje hodnoty z formuláře a přidá diagnozu k návštěvě nebo upraví stávající záznam
     * @param   Nette\Application\UI\Form     odeslaný formulář
     * @return  void
     */   
    public function submitDiagnozaForm($form) {
        $values = $form->getValues();
        $id = (int) $values['id'];
        $navsteva_id = $values['navsteva_id'];
        unset($values['id']);
        unset($values['diagnoza_name']);
        
        if ($id > 0) {            
            unset($values['navsteva_id']);
            $this->getService('model')->getDiagnoza($id)->update($values);
            $this->flashMessage('Diagnóza byla aktualizována.');
        } else {
            $this->getService('model')->addDiagnoza($values);
            $this->flashMessage('Diagnóza byla přidána k návštěvě.');
        }        
        $this->redirect('Navsteva:view',$navsteva_id);
    }
    
    /**
    * Továrnička pro tvorbu formuláře terapie 
    * @param string     název komponenty
    * @return Nette\Application\UI\Form
    */
    protected function createComponentTerapieForm($name) {
            $form = new Nette\Application\UI\Form;
            
            $form->addHidden('id');
            $form->addHidden('navsteva_id',$this->getParameter('id'));
            $form->addText('terapie', 'Terapie',40)
                    ->setRequired('Vyplňte terapii')
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka je %d znaků', 255);
            $form->addText('lecivo', 'Léčivo',40)
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka je %d znaků', 45);
            $form->addText('pristroj', 'Přístroj',40)
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka je %d znaků', 45);
            $form->addText('material', 'Materiál',40)
                    ->addRule(Form::MAX_LENGTH, 'Maximální délka je %d znaků', 255);
            $form->addTextArea('komentar', 'Komentář');

            $form->addSubmit('submitButton', 'Uložit');
            $form->onSuccess[] = callback($this, 'submitTerapieForm');

            return $form;
        }    
    
     /**
     * Zpracuje hodnoty z formuláře a přidá terapii k návštěvě nebo upraví stávající záznam
     * @param   Nette\Application\UI\Form     odeslaný formulář
     * @return  void
     */   
     public function submitTerapieForm($form) {
        $values = $form->getValues();
        $id = (int) $values['id'];
        $navsteva_id = $values['navsteva_id'];
        
        unset($values['id']);

        if ($id > 0) {
            unset($values['navsteva_id']);
            $this->getService('model')->getTerapie($id)->update($values);
            $this->flashMessage('Terapie byla akualizována.');
        } else {
            $this->getService('model')->addTerapie($values);
            $this->flashMessage('Terapie byla přidána k návštěvě.');
        }        
        $this->redirect('Navsteva:view',$navsteva_id);
        
    }
    
    /**
    * Továrnička pro tvorbu formuláře zdravotních informací
    * @param string     název komponenty
    * @return Nette\Application\UI\Form
    */
    protected function createComponentInfoForm($name) {
            $form = new Nette\Application\UI\Form;
            
            $form->addHidden('id');
            $form->addHidden('navsteva_id',$this->getParameter('id'));
            
            $form->addText('vyska', 'Výška',3)                   
                ->addRule(Form::INTEGER, 'Výška musí být číslo')
                ->addRule(Form::RANGE, 'Výška musí být od %d do %d', array(10, 300))
                ->setAttribute('type','number')
                 ->setOption('description', 'cm');
            
            $form->addText('vaha', 'Váha',3)                   
                ->addRule(Form::INTEGER, 'Váha musí být číslo')
                ->addRule(Form::RANGE, 'Váha musí být od %d do %d', array(1, 500))
                ->setAttribute('type','number')
                 ->setOption('description', 'kg');
            
            $form->addSelect('koureni_id', 'Kouření',
                    $this->getService('model')->getSelectArray('koureni')
            );
            
            $form->addText('alergie', 'Alergie',15)                   
                ->addRule(Form::MAX_LENGTH, 'Popis alerige musí být dlouhý max. %d znaků',30);

            $form->addText('tlak_s', 'Systolický tlak',3)                   
                ->addRule(Form::INTEGER, 'Tlak musí být číslo')
                ->addRule(Form::RANGE, 'Tlak musí být od %d do %d', array(1, 500))
                ->setAttribute('type','number')
                ->setOption('description', 'mmHg');
            
            $form->addText('tlak_d', 'Diastolický tlak',3)                   
                ->addRule(Form::INTEGER, 'Tlak musí být číslo')
                ->addRule(Form::RANGE, 'Tlak musí být od %d do %d', array(1, 500))
                ->setAttribute('type','number')
                ->setOption('description', 'mmHg');
            
            $form->addText('tep', 'Tep',3)                   
                ->addRule(Form::INTEGER, 'Tep musí být číslo')
                ->addRule(Form::RANGE, 'Tep musí být od %d do %d', array(1, 500))
                ->setAttribute('type','number')
                ->setOption('description', 'tepů za minutu');
            
            $form->addText('teplota', 'Teplota',3)                   
                ->addRule(Form::FLOAT, 'Teplota musí být číslo')
                ->addRule(Form::RANGE, 'Teplota musí být od %d do %d', array(20, 80))
                ->setAttribute('type','number')
                ->setOption('description', '°C');
            
            $form->addCheckbox('tehotenstvi', 'Těhotenstvi');
                    
            $form->addSubmit('submitButton', 'Uložit');
            $form->onSuccess[] = callback($this, 'submitInfoForm');

            return $form;
        }    
    
     /**
     * Zpracuje hodnoty z formuláře a přidá zdravotní informace k návštěvě
     * @param   Nette\Application\UI\Form     odeslaný formulář
     * @return  void
     */
     public function submitInfoForm($form) {
        $values = $form->getValues(TRUE);
        $id = (int) $values['id'];
        $navsteva_id = $values['navsteva_id'];

        unset($values['id']);

        $values = array_filter($values,'strlen');
        
        if ($id > 0) {
            unset($values['navsteva_id']);
            $this->getService('model')->getInfoByNavsteva($navsteva_id)->update($values);
            $this->flashMessage('Zdravotní informace byli aktualizovány.');
        } else {
            $this->getService('model')->addInfo($values);
            $this->flashMessage('Zdravotní informace byli přidány.');
        }        
        $this->redirect('Navsteva:view',$navsteva_id);
        
    }

    /**
    * Továrnička pro tvorbu formuláře na připojení zaměstnance k návštěvě.
    * @param string     název komponenty
    * @return Nette\Application\UI\Form
    */
    protected function createComponentZamestnanecForm($name) {
        $form = new  Nette\Application\UI\Form;

        $form->addHidden('navsteva_id',$this->getParameter('id'));

        $form->addHidden('zamestnanec_id')
            ->setRequired('Vyberte zaměstnance ze seznamu');

        $form->addText('zamestnanec_name', 'Zaměstnanec',30)                   
            ->setRequired('Vyplňte jméno zaměstnance')
            ->setAttribute('placeholder','napište alespoň 3 písmena');

        $form->addSubmit('submitButton', 'Přidat');
        $form->onSuccess[] = callback($this, 'submitZamestnanecForm');

        return $form;
    }
    
     /**
     * Zpracuje hodnoty z formuláře a přidá zaměstnance k návštěvě
     * @param   Nette\Application\UI\Form     odeslaný formulář
     * @return  void
     */
    public function submitZamestnanecForm($form) {
        $zamestnanec_id = $form['zamestnanec_id']->value;             
        $navsteva_id = $form['navsteva_id']->value;

        $this->getService('model')->addZamestnanec($zamestnanec_id, $navsteva_id);
        $this->flashMessage('Zaměstananec byl přidán k návštěvě.');
        $this->redirect('Navsteva:view',$navsteva_id);
    }    
    
    
}