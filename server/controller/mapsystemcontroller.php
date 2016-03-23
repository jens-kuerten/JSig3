<?php

namespace Controller;
class MapSystemController {
    
    protected static $wormholeClasses;
    
    public static function solarSystemNameToID($solarSystemName) {
        if (!is_string($solarSystemName)) {
            return false;
        }
        $system = new \Model\MapSystem($solarSystemName);
        $solarSystemID = $system->solarSystemID();
        if (is_numeric($solarSystemID) AND $solarSystemID!==0) {
            return $solarSystemID;
        }
        return false;
    }
    
    /**
     * checks if the systems are connected by a gate
     * @param \Model\MapSystem $fromMapSystem
     * @param \Model\MapSystem $toMapSystem
     * @return boolean
     */
    public static function haveGateConnection(\Model\MapSystem $fromMapSystem,  \Model\MapSystem $toMapSystem) {
        $gates = $fromMapSystem->gates();
        if ($gates !=null) {
            foreach($gates as $gateSolarSystemID) {
                if ($gateSolarSystemID == $toMapSystem->solarSystemID()) {
                    return true;
                }
            }    
        }        
       
        //gates are bidirectional -> no need to check from the other side too
        return false;
    }
    
    public static function deleteSolarSystem($solarSystemID,$tabID) {
        $prefix = \Db\Sql::dbPrefix();
        
        //delete the system itself
        $query = 'DELETE FROM '.$prefix.'mapsystem WHERE solarSystemID = :solarSystemID AND tabID = :tabID';
        $systemDelete = \Db\Sql::execute($query,[
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID
        ]);
        
        //delete connections to and from it
        $query = 'DELETE FROM '.$prefix.'connection WHERE tabID = :tabID AND (fromSolarSystemID = :solarSystemID OR toSolarSystemID = :solarSystemID)';
        $connectionDelete = \Db\Sql::execute($query,[
            ':tabID'=>$tabID,
            ':solarSystemID'=>$solarSystemID
        ]);
        if ($systemDelete>0) {
            self::sendPipeTabDeleteSystem($solarSystemID, $tabID);
            return true;
        }
        return false;
    }
    
    /**
     * moves a system
     * @param integer $solarSystemID
     * @param integer $tabID
     * @param array $mapPosition [0]=>x,[1]=>1
     */
    public static function moveSolarsystem($solarSystemID,$tabID,$mapPosition) {
        $prefix  = \Db\Sql::dbPrefix();
        
        $query = 'UPDATE '.$prefix.'mapsystem SET mapX = :mapX, mapY = :mapY WHERE '
                . 'solarSystemID = :solarSystemID AND tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[
            ':mapX'=>$mapPosition[0],
            ':mapY'=>$mapPosition[1],
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID
        ]);
        if ($rowCount>0) {
            self::sendPipeTabMoveSystem($solarSystemID, $tabID, $mapPosition);
            return true;
        }
        return false;
    }       
    
    /**
     * edits a systems label,
     * @param integer $solarSystemID
     * @param integer $tabID
     * @param string $label
     * @return boolean
     */
    public static function setLabel($solarSystemID,$tabID,$label) {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'UPDATE '.$prefix.'mapsystem SET label = :label WHERE '
                . 'solarSystemID = :solarSystemID AND tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID,
            ':label'=>$label
        ]);
        if ($rowCount>0) {
            self::sendPipeTabSetLabel($solarSystemID, $tabID, $label);
            return true;
        }
        return false;
    }
    
    public static function setRallypoint($solarSystemID,$tabID,$state) {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'UPDATE '.$prefix.'mapsystem SET rallypoint = :state WHERE '
                . 'solarSystemID = :solarSystemID AND tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID,
            ':state'=>$state
        ]);
        if ($rowCount>0) {
            self::sendPipeTabSetSystemStatus($solarSystemID, $tabID, 'rallypoint',$state);
            return true;
        }
        return false;
    }
    
    public static function setHome($solarSystemID,$tabID,$state) {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'UPDATE '.$prefix.'mapsystem SET home = :state WHERE '
                . 'solarSystemID = :solarSystemID AND tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID,
            ':state'=>$state
        ]);
        if ($rowCount>0) {
            self::sendPipeTabSetSystemStatus($solarSystemID, $tabID, 'home',$state);
            return true;
        }
        return false;
    }
    
    public static function setLocked($solarSystemID,$tabID,$state) {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'UPDATE '.$prefix.'mapsystem SET locked = :state WHERE '
                . 'solarSystemID = :solarSystemID AND tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID,
            ':state'=>$state
        ]);
        if ($rowCount>0) {
            self::sendPipeTabSetSystemStatus($solarSystemID, $tabID, 'pin',$state);
            return true;
        }
        return false;
    }
    
    /**
     * returns the label for the $toMapSystem
     * @param \Model\MapSystem  $fromMapSystem where the connection is made from
     * @param \Model\MapSystem  $toMapSystem where the connection is made to
     * @param \Model\Tab $tab the tab where the connection is made
     * @return string label
     */
    public static function getNewLabel(\Model\MapSystem $toMapSystem, \Model\Tab $tab, \Model\MapSystem $fromMapSystem= null) {
        $chainLetterPriority = \Server::get('conf_chainletters');
        $chainletter = '';
        $chainnumber = '';        
        
        $classLabel = self::getClassLabel($toMapSystem->solarSystemClass());
        if ($fromMapSystem==null) {            
            //fromSystem is not on the tab -> label for new system is simply the class
            //if it's the first system add '.1'  
            //if ($tab->getCountSolarSystems()==0) $classLabel .='.1';
            return $classLabel;
        }        
        //check if the from system is marked as home
        if ($fromMapSystem->home()==true) {
            //generate a new chain            
            if ( (self::hasStaticTargetClass($fromMapSystem, $toMapSystem->solarSystemClass())) AND (!self::containsLabel($tab, $classLabel.'.1')) ) {
                //if the to System is the static make it the main chain
                $chainletter = '';
                $chainnumber = 1;
            }else{                
                //if it's not a static it's an incoming chain -> give it a new letter                
                $chainletter = self::getNewChainLetter($tab, $chainLetterPriority);
                $chainnumber = '';
            }
        }else{            
            //get the chain letter of the fromSystem
            $chainletter = self::returnChainLetter($fromMapSystem->mapLabel());
            
            if ($chainletter===false) {
                //couldn't find the chainletter because label is some weird shit -> return just the classlabel
                return $classLabel;
            }            

            //get the highest systemnumber of the systems with that classlabel and chainletter
            $chainnumber = self::getNextChainNumber($tab, $chainletter, $classLabel);
        }
        return $classLabel.'.'.$chainletter.$chainnumber;
    }
        
    public static function getClassLabel($class) {
        switch ($class) {
            case (1):
                return 'C1';
                break;
            case (2):
                return 'C2';
                break;
            case (3):
                return 'C3';
                break;
            case (4):
                return 'C4';
                break;
            case (5):
                return 'C5';
                break;
            case (6):
                return 'C6';
                break;
            case (7):
                return 'HS';
                break;
            case (8):
                return 'LS';
                break;
            case (9):
                return 'NS';
                break;
            case (31):
                return 'C1';
                break;
            case (32):
                return 'C2';
                break;
            case (33):
                return 'C3';
                break;
            case (34):
                return 'C4';
                break;
            case (35):
                return 'C5';
                break;
            case (36):
                return 'C6';
                break;
            case (40):
                return 'TH';
                break;
            case (41):
                return 'C13';
                break;
            case (42):
                return 'C13';
                break;
            case (43):
                return 'C13';
                break;
            default:
                return $class;
                break;
            
        }
    }
    
    
    /**
     * checks if the mapsystem has a static that leads to the class
     * @param \Model\MapSystem $mapSystem
     * @param integer $class
     * @return boolean
     */
    public static function hasStaticTargetClass(\Model\MapSystem $mapSystem, $class) {
        $statics = $mapSystem->statics();        
        if ($statics!==null) {
            foreach($mapSystem->statics() as $identifier) {                
                if (self::getWormholeTargetClass($identifier)==$class) {
                    return true;
                }
                return false;
            }
        }
        return false;
        
    }
    /**
     * returns the class of a wormhole, null if not found
     * @param string $identifier
     * @return integer
     */
    public static function getWormholeTargetClass($identifier) {
        if (self::$wormholeClasses==null) {
            self::loadWormholes();
        }
        if (isset(self::$wormholeClasses[$identifier])) {
            return self::$wormholeClasses[$identifier];
        }
        return null;
    }
    
    protected static function getNewChainLetter(\Model\Tab $tab,$letterPriority) {
        $pointer = 0;
        for ($pointer = 0; $pointer<strlen($letterPriority); $pointer++) {
            $chainletter = substr($letterPriority, $pointer,1);
            if (!self::containsChainLetter($tab, $chainletter)) {
                return $chainletter;
            }
        }
        //if we run out of chainletters use x as chainletter - not really realistic unless EVE is bugged... well, ok it IS realistic
        return 'x';
    }
    
    protected static function containsChainLetter(\Model\Tab $tab,$chainletter) {
        $tabSystems = $tab->returnMapSystems();
        foreach ($tabSystems as $tabSystem) {
            $systemChainLetter = self::returnChainLetter($tabSystem->mapLabel());
            if ( ($systemChainLetter!==false) AND ($systemChainLetter==$chainletter)) {
                return true;
            }
        }
        return false;
    }
    
    protected static function containsLabel(\Model\Tab $tab,$label) {
        $tabSystems = $tab->returnMapSystems();
        foreach ($tabSystems as $tabSystem) {
            if ($tabSystem->mapLabel() == $label) {
                return true;
            }
        }
        return false;
    }
    
    protected static function returnChainLetter($label) {
        //get the stuff behind the first dot to continue the chain
        $dotposition = strpos($label,'.');
        //if from label is some wierd shit return false
        if ($dotposition===false) {            
            return false;
        }

        $stuff = substr($label, $dotposition+1); //stuff behind the first dot        
        //now retrieve the letter from stuff     
        $chainletter = null;
        if (is_numeric($stuff)) { 
            $chainletter = ''; //we are on the main chain -> no letter
        }else{
            $stuffletter = substr($stuff,0,1); //first character should be a letter
            $stuffnumber = substr($stuff,1); //rest should be empty(false) or a number
            if ( (!is_numeric($stuffletter) AND ( (is_numeric($stuffnumber)) OR ($stuffnumber===false) )) ) { //check if that is the case
                $chainletter = $stuffletter;
            }else{
                //label is sum weird shit -> return false
                return false;
            }
        }
        return $chainletter;
    }
    
    protected static function getNextChainNumber(\Model\Tab $tab,$chainletter,$classlabel) {
        $highestChainNumber = 0;
        $tabSystems = $tab->returnMapSystems();
        $searchLabel = $classlabel.'.'.$chainletter;
        foreach ($tabSystems as $tabSystem) {            
            if ( strpos($tabSystem->mapLabel(),$searchLabel)===0 ) { //$label needs to match at first position                
                //found an occurence, now get the number
                $number = substr($tabSystem->mapLabel(), strlen($searchLabel));
                if (is_numeric($number)) {
                    $highestChainNumber<$number ? $highestChainNumber=$number : false;
                }else
                if ($number=='') {
                    $highestChainNumber<1 ? $highestChainNumber=1 : false;
                }
            }
        }         
        /*         
        * if we found at least one occurence just append count +1 to label (e.g. C5.x2 or C5.2)
        * if we found no occurence and we are on the main chain append 1 (e.g. C4.1)
        * if it has a letter and is the first just leave it at that (e.g. C6.y)
        */
        $chainnumber = '';
        if ($highestChainNumber >0) {
            $chainnumber=($highestChainNumber+1);
        }else if( ($highestChainNumber===0) AND ($chainletter=='') ) {
            $chainnumber=1;
        }
        return $chainnumber;
    }
    
    protected static function loadWormholes() {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'SELECT identifier,targetClass FROM '.$prefix.'wormhole';
        $result = \Db\Sql::query($query);
        
        foreach ($result as $row) {
            self::$wormholeClasses[$row['identifier']] = $row['targetClass'];
        }
        return true;
    }
    
    protected static function sendPipeTabDeleteSystem($solarSystemID,$tabID) {
        \Controller\PipeController::sendTabMessage($tabID, 'deleteSystem', ['solarSystemID'=>$solarSystemID,'tabID'=>$tabID]);
    }
    
    protected static function sendPipeTabMoveSystem($solarSystemID,$tabID,$mapPosition) {
        \Controller\PipeController::sendTabMessage($tabID, 'moveSystem', ['solarSystemID'=>$solarSystemID,'tabID'=>$tabID,'mapX'=>$mapPosition[0],'mapY'=>$mapPosition[1]]);
    }
    
    protected static function sendPipeTabSetLabel($solarSystemID,$tabID,$label) {
        \Controller\PipeController::sendTabMessage($tabID, 'setLabel', ['solarSystemID'=>$solarSystemID,'tabID'=>$tabID,'label'=>$label]);
    }
    
    protected static function sendPipeTabSetSystemStatus($solarSystemID,$tabID,$systemStatus,$state) {
        \Controller\PipeController::sendTabMessage($tabID, 'setSystemStatus', ['solarSystemID'=>$solarSystemID,'tabID'=>$tabID,'systemStatus'=>$systemStatus,'state'=>$state]);
    }
}
