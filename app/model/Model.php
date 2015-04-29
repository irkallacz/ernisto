<?php
/**
 * Tento soubor je součástí projektu ErNISTo z předmetu A6N33LI Lékařská informatika
 * v 1. ročníku magisterského studia oboru Biomedicínského inženýrství     
 */

/**
 * Model je základní službou (services) pro přístup k databázi. Využívá autowritingu pro inicializaci 
 * a většina jeho metod vrací instanci Nette\Database\Table\ActiveRow zapouzdřující jeden řádek z tabulky nebo
 * Nette\Database\Table\Selection pro více řádků.  
 * 
 * @author     Jakub Mottl
 */

class Model extends Nette\Object
{
   
    /** @var Nette\Database\Connection */
    public $database;

    /** 
    */
    public function __construct(Nette\Database\Connection $database){
          $this->database = $database;
    }
    
   /**
   * Vrací pole ($id => $value) z tabulky $table pro SelectBox seřazené abecedně 
   * @param     string   Název tabulky
   * @return    array
   */
    public function getSelectArray($table){
        return $this->database->table($table)->select('id, nazev')->order('nazev')->fetchPairs('id','nazev'); 
    }
        
   /**
   * Vrací návštěvu podle jejího ID
   * @param     int   ID návštevy
   * @return    Nette\Database\Table\ActiveRow
   */   
    public function getNavsteva($id){
        return $this->database->table('navsteva')->get($id); 
    }
    
   /**
   * Vrací seznam návštěv, které odpovídají zadanému datu.
   * @param     Datetime   Datum podle kterého se mají vybrat návštevy
   * @return    Nette\Database\Table\Selection|NULL
   */
    public function getNavstevaByDate($date){
        return $this->database->table('navsteva')
                ->where('DATEDIFF(datum, ?) = 0',$date->format($date->format('Y-m-d')))
                ->order('datum DESC');
    }
  
   /**
   * Vrací seznam všech návštěv pacienta podle jeho ID
   * @param     int   ID pacienta
   * @return    Nette\Database\Table\Selection|NULL
   */   
    public function getNavstevaByPacient($id){
        return $this->database->table('navsteva')->where('pacient_id',$id)->order('datum'); 
    }
    
    /**
   * Vrací všechny návštevy, které vyhovují zadanému řetězci
   * @param     string   Hledaný řetězec
   * @return    Nette\Database\Table\Selection 	
   */
    public function getNavstevaByTerm($term){    
    return $this->database->table('navsteva')
                ->where("datum LIKE ? OR pacient.prijmeni LIKE ? OR pacient_id LIKE ? OR oddeleni.nazev LIKE ? OR pracoviste.nazev LIKE ? OR cislo_mistnosti LIKE ?",
                        '%'.$term.'%','%'.$term.'%','%'.$term.'%','%'.$term.'%','%'.$term.'%','%'.$term.'%')
                ->order('datum DESC');
    }
    
   /**
   * Vloží novou návštěvu do tabulky a vrátí nový řádek
   * @param     array   Pole hodnot array($column => $value)
   * @return    Nette\Database\Table\ActiveRow
   */
    public function addNavsteva($values){
        return $this->database->table('navsteva')->insert($values); 
    }
   
   /**
   * Vrací diagnozu podle jejího ID
   * @param     int   ID diagnozy
   * @return    Nette\Database\Table\ActiveRow
   */
    public function getDiagnoza($id){
        return $this->database->table('diagnoza')->get($id); 
    }

   /**
   * Vloží novou diagnozu do tabulky a vrátí nový řádek
   * @param     array   Pole hodnot array($column => $value)
   * @return    Nette\Database\Table\ActiveRow
   */
    public function addDiagnoza($values){
        return $this->database->table('diagnoza')->insert($values); 
    }
   
   /**
   * Vrací všechny diagnozy, které vyhovují zadanému řetězci
   * @param     string   Hledaný řetězec
   * @return    Nette\Database\Table\Selection 	
   */    
    public function getDiagnozaByTerm($term){
        return $this->database->table('mkn10')->where('nazev LIKE ? OR id LIKE ?',$term.'%',$term.'%')->order('nazev'); 
    }

   /**
   * Vrací diagnozu podle jejího ID
   * @param     int   ID diagnozy
   * @return    Nette\Database\Table\ActiveRow
   */   
    public function getTerapie($id){
        return $this->database->table('terapie')->get($id); 
    }

   /**
   * Vloží novou terapii do tabulky a vrátí nový řádek
   * @param     array   Pole hodnot array($column => $value)
   * @return    Nette\Database\Table\ActiveRow
   */
    public function addTerapie($values){
        return $this->database->table('terapie')->insert($values); 
    }
    
    /**
   * Vrací seznam zdrav. informací podle ID návštěvy
   * @param     int   ID pacienta
   * @return    Nette\Database\Table\Selection|NULL
   */      
    public function getInfoByNavsteva($id){
        return $this->database->table('zdrav_info')->where('navsteva_id',$id)->fetch(); 
    }

   /**
   * Vloží nové zdrav. info do tabulky a vrátí nový řádek
   * @param     array   Pole hodnot array($column => $value)
   * @return    Nette\Database\Table\ActiveRow
   */   
    public function addInfo($values){
        return $this->database->table('zdrav_info')->insert($values); 
    }

   /**
   * Vrací pacienta podle jejího ID
   * @param     int   ID Pacienta
   * @return    Nette\Database\Table\ActiveRow|NULL
   */      
    public function getPacient($id){
        return $this->database->table('pacient')->get($id);
    }
 
    /**
   * Vrací všechny pacienty, které vyhovují zadanému řetězci
   * @param     string   Hledaný řetězec
   * @return    Nette\Database\Table\Selection|NULL
   */    
    public function getPacientByTerm($term){
        return $this->database->table('pacient')
                ->where('prijmeni LIKE ? OR id LIKE ? OR jmeno LIKE ? OR ulice LIKE ? OR mesto LIKE ? OR psc LIKE ? OR cp LIKE ? OR pojistovna_id LIKE ?',
                        $term.'%',$term.'%',$term.'%',$term.'%',$term.'%',$term.'%',$term.'%',$term.'%')
                ->order('prijmeni'); 
    }
        
   /**
   * Vloží nového pacienta do tabulky a vrátí nový řádek
   * @param     array   Pole hodnot array($column => $value)
   * @return    Nette\Database\Table\ActiveRow
   */        
    public function addPacient($values){
        return $this->database->table('pacient')->insert($values); 
    }
    
   /**
   * Připojí zaměstnance k návštěve
   * @param     int   ID Návštěvy
   * @param     int   ID Návštěvy
   * @return    Nette\Database\Table\ActiveRow
   */        
    public function addZamestnanec($zamestnanec_id, $navsteva_id){
        $values = array('zamestnanec_id' => $zamestnanec_id, 'navsteva_id' => $navsteva_id);
        return $this->database->table('navsteva_has_zamestnanec')->insert($values); 
    }    
        
   /**
   * Odebere zaměstnance od návštěvy
   * @param     int   ID Návštěvy
   * @param     int   ID Návštěvy
   * @return    Nette\Database\Table\ActiveRow
   */           
    public function deleteZamestnanec($zamestnanec_id, $navsteva_id){
        $values = array('zamestnanec_id' => $zamestnanec_id, 'navsteva_id' => $navsteva_id);
        return $this->database->table('navsteva_has_zamestnanec')->where($values)->delete(); 
    }    
   
    /**
   * Vrací všechny zaměstnance, kteří vyhovují zadanému řetězci
   * @param     string   Hledaný řetězec
   * @return    Nette\Database\Table\Selection|NULL
   */
    public function getZamestnanecByTerm($term){
        return $this->database->table('zamestnanec')
                ->where('prijmeni LIKE ? OR titul_pred LIKE ? OR titul_za LIKE ? OR jmeno LIKE ? OR oddeleni.nazev LIKE ?',
                        $term.'%',$term.'%',$term.'%',$term.'%',$term.'%')
                ->order('prijmeni'); 
    }
    
//    public function createAuthenticatorService(){
//        return new Authenticator($this->database->table('betausers'));
//    }

}
