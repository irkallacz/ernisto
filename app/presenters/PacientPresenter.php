<?php
/**
 * Tento soubor je součástí projektu ErNISTo z předmetu A6N33LI Lékařská informatika
 * v 1. ročníku magisterského studia oboru Biomedicínského inženýrství     
 */

use Nette\Forms\Form,
    Nette\Diagnostics\Debugger;

/**
 * Presenter Pacienta. Má na starost ipředevším vyktreslování inforamcí a také mazání a vyhledávání pacienta.
 *
 * @author Jakub Mottl
 */
class PacientPresenter extends BasePresenter {
   
    /**
    * Vykreslí pacienta podle ID, pokud není předáno, přesměruje na vyhledávání. Z modelu si natáhne potřebné informace, 
    * dopočítá datum narození, věk a seskupí zdravotní informace z jednotlivých návštěv k sobě.  
    * @param    int ID pacienta
    * @return   void
    */ 
    public function renderView($id) {      
      if (is_null($id)) $this->redirect ('Pacient:');
      
      $this->template->id = $id;
      $pacient = $this->getService('model')->getPacient($id);
      $born_date = DateTime::createFromFormat('ymd',substr($pacient->id, 0, 6));
      $pacient->vek = $born_date->diff(new DateTime)->format('%Y');
      $pacient->born_date = $born_date;
      
      $this->template->pacient = $pacient;
      $navstevy = $pacient->related('navsteva')->order('datum');
      $this->template->navstevy = $navstevy;
      
      $info = array();
          
        foreach ($navstevy as $navsteva){
            foreach ($navsteva->related('zdrav_info') as $data){
                foreach ($data as $key => $value){
                  if((!is_null($value))and($key!='navsteva_id')and($key!='id')){
                    switch ($key) {
                        case "tehotenstvi":
                             $info[$key][$navsteva->id]['datum'] = $navsteva->datum;
                             $info[$key][$navsteva->id]['value'] = $value ? 'ano' : 'ne';
                            break;
                        case "tlak_s":
                            if ((isset($data->tlak_s))and(isset($data->tlak_d))){
                                $info['tlak'][$navsteva->id]['datum'] = $navsteva->datum;
                                $info['tlak'][$navsteva->id]['value'] = $data->tlak_s.'/'.$data->tlak_d;
                            }
                            break;
                        case "tlak_d":
                            break;
                        default:
                            $info[$key][$navsteva->id]['datum'] = $navsteva->datum;
                            $info[$key][$navsteva->id]['value'] = $value;
                    }
                    
                  }
              }
          }
       
       $this->template->info = $info;        
      }
    }
    
    /**
    * Smaže pacienta, vypíše zprávu a přesměruje na vyhledávání. 
    * @param    int ID pacienta
    * @return   void
    */ 
    public function actionDeletePacient($id) {
          $this->getService('model')->getPacient($id)->delete();
          $this->flashMessage('Pacient byl smazán');
          $this->redirect('Pacient:default');
    }
    
    /**
    * Vyhledávání pacienta. Předá vyvledávaný termín do modelu a vrácené řádky s pacienta předá do šablony
    * @param    string hledaný termín
    * @return   void
    */ 
    public function actionAutocompletePacient($term) {
        $this->template->list = $this->getService('model')->getPacientByTerm($term);
    }
    
    /**
    * Vyhledávání pacienta. Předá vyvledávaný termín do modelu a vrácené řádky s pacienta předá do šablony
    * @param    string hledaný termín
    * @return   void
    */ 
    public function actionSearchPacient($term) {
        $this->template->list = $this->getService('model')->getPacientByTerm($term);
    }
}