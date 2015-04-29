<?php
/**
 * Tento soubor je součástí projektu ErNISTo z předmetu A6N33LI Lékařská informatika
 * v 1. ročníku magisterského studia oboru Biomedicínského inženýrství 
 *     
 */
use Nette\Diagnostics\Debugger;
/**
 * Presenter Čekárny. Má na starost vykreslení čekárny se seznamem pacientů
 * 
 * @author Jakub Mottl  
 */
class CekarnaPresenter extends BasePresenter
{	
    /**
    * Vykreslí čekárnu se seznamem pacientů avšak pouze pro naší nemocnici a na oddelení chirurgie.
    * @param    string  datum ve formátu Y-m-d
    */
    public function renderView($date = null){
        $date = new DateTime($date);
        $this->template->navstevy = $this->context->getService('model')->getNavstevaByDate($date)
                ->where('pracoviste_id','00829838000')
                ->where('oddeleni_id','chi');
        $this->template->datum = $date;
        $this->template->now = new DateTime;           
        Debugger::barDump($this->context->getService('model')->database->supplementalDriver->getColumns('pacient'));
    }
    
}
