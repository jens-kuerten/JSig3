<?php

namespace Controller;

class TabController extends BaseController{
    protected static $tabs = [];
    
    /**
     * adds system to the tab, returns system when successfull;
     * returns false if system already exists
     * @param \Model\MapSystem $mapSystem
     * @param \Model\Tab $tab
     * @return boolean|\Model\MapSystem
     */
    public static function addSystem(\Model\MapSystem $mapSystem, \Model\Tab $tab) {
        $mapSystem->tabID($tab->tabID());
        if ($mapSystem->mapPosition()==null) {
            $mapSystem->mapPosition(self::findFreePosition($tab));
        }
        if ($mapSystem->mapLabel()==null) {
            $mapSystem->mapLabel(\Controller\MapSystemController::getNewLabel($mapSystem, $tab));
        }        
        if ($mapSystem->create()) {            
            //system added to tab ->inform client
            self::sendPipeTabAddSystem($mapSystem,$tab->tabID());
            
            //load intel, POIs and signatures and send the
            $pois = $mapSystem->returnPois();
            foreach($pois as $poi) {
                self::sendPipeTabAddPoi($poi,$tab->tabID());
            }
            
            $intels = $mapSystem->returnIntels();
            foreach($intels as $intel) {
                self::sendPipeTabAddIntel($intel,$tab->tabID());
            }
            
            $signatures = $mapSystem->returnSignatures();
            foreach($signatures as $signature) {
                self::sendPipeTabAddSignature($signature,$tab->tabID());
            }            
            return $mapSystem;
        }else{            
            return false;
        }
        
    }
    
    protected static function addConnection(\Model\MapSystem $fromMapSystem, \Model\MapSystem $toMapSystem, \Model\Tab $tab) {
        //look for a similar connection in the tabs groupID to copy over it's stats
        $connection = \Controller\ConnectionController::returnSisterConnection($fromMapSystem->solarSystemID(), $toMapSystem->solarSystemID(), $tab->tabID());        
        if ($connection!==null) {
            //found a sister connection -> set it to this tabID and create it
            $connection->tabID($tab->tabID());
            if ($connection->create()) {
                //if successfully created inform other clients via the pipe
                self::sendPipeTabAddConnection($connection, $tab->tabID());
                //log connection
                $accountID = \Controller\SessionController::session()->accountID();
                \Lib\Logger::logConnection($fromMapSystem->solarSystemID(), $toMapSystem->solarSystemID(), $accountID, $tab->groupID());
                return true;
            }
        }else{
            //nope, no similar connection exists -> create a new one
            $connection = new \Model\Connection($fromMapSystem->solarSystemID(), $toMapSystem->solarSystemID(), $tab->tabID());
            $connection->massPassed(0);
            $connection->massStage(1);
            $connection->timeStage(1);
            $connection->creationTime(time());
            if ($connection->create()) {
                //if successfully created inform other clients via the pipe
                self::sendPipeTabAddConnection($connection, $tab->tabID());
                //log connection
                $accountID = \Controller\SessionController::session()->accountID();
                \Lib\Logger::logConnection($fromMapSystem->solarSystemID(), $toMapSystem->solarSystemID(), $accountID, $tab->groupID());
                return true;
            }
        }
        return false;
        
    }
    
    public static function makeConnection(\Model\MapSystem $fromMapSystem, \Model\MapSystem $toMapSystem, \Model\Tab $tab) {
        $tabMapSystems = $tab->returnMapSystems();
        //check if first mapsystem is already on the tab
        $found = false;
        foreach ($tabMapSystems as $tabMapSystem) {
            if ($tabMapSystem->solarSystemID() == $fromMapSystem->solarSystemID()) {                
                $found = true;
                $fromMapSystem = $tabMapSystem; //properties of the fromMapSystem should now be set
                break;
            }
        }
        //if it isn't add it
        if (!$found) {
            self::addSystem($fromMapSystem, $tab);
        }        
        
        //add the second mapsystem to the tab
        $toMapSystem->mapPosition(self::findFreePosition($tab,$fromMapSystem));        
        $toMapSystem->mapLabel(\Controller\MapSystemController::getNewLabel($toMapSystem, $tab,$fromMapSystem));
        self::addSystem($toMapSystem, $tab);
        
        //add the connection
        self::addConnection($fromMapSystem, $toMapSystem, $tab);
        
        $mass = \Lib\Sde::getMass(\Lib\IgbApi::getHeaderShipTypeName());
        
        \Controller\ConnectionController::addMass($fromMapSystem->solarSystemID(), $toMapSystem->solarSystemID(), $tab, $mass);
        
        return true;
    }
       
    public static function CopyChain(\Model\Tab $fromTab, \Model\Tab $toTab, $solarSystemIDs, $deleteSource) {
        $fromTabSystems = $fromTab->returnMapSystems();
        $fromTabConnections = $fromTab->returnConnections();
        //$fromTabSignatures = $fromTab->returnSignatures();
        
        //add selected solar System to the target Tab
        foreach($fromTabSystems as $mapSystem) {            
            if (array_search($mapSystem->solarSystemID(), $solarSystemIDs)!==false) {
                \Controller\TabController::addSystem($mapSystem, $toTab);
            }
        }
        
        //add connections
        foreach ($fromTabConnections as $connection) {
            if (array_search($connection->fromSolarSystemID(), $solarSystemIDs)!== false AND 
                    array_search($connection->toSolarSystemID(), $solarSystemIDs)!== false) {
                $connection->tabID($toTab->tabID());
                if ($connection->create()) {
                    //if successfully created inform other clients via the pipe
                    self::sendPipeTabAddConnection($connection, $toTab->tabID());                
                }
            }
        }
        
        if ($deleteSource==true) {
            foreach($solarSystemIDs as $solarSystemID) {
                \Controller\MapSystemController::deleteSolarSystem($solarSystemID, $fromTab->tabID());
            }
        }
                
    }
    
    /**
     * returns a free mapPosition
     * @param \Model\Tab $tab
     * @param \Model\MapSystem $aroundSolarSystem
     * @return array
     */
    protected static function findFreePosition(\Model\Tab $tab, \Model\MapSystem $aroundSolarSystem = null) {
        //get systems
        $mapSystems = $tab->returnMapSystems();
        $offsetX = \Server::get('conf_systemOffsetX');
        $offsetY = \Server::get('conf_systemOffsetY');
        
        $offsetXhalf = round($offsetX/2);
        $offsetYhalf = round($offsetY/2);        
        $startingPosition = [0,0];
        
        if ($aroundSolarSystem!==null) {
            if ($aroundSolarSystem->mapPosition()!==null) {               
                $startingPosition = $aroundSolarSystem->mapPosition();                
            }else {
                foreach($mapSystems as $mapSystem) {
                    if ($mapSystem->solarSystemID() == $aroundSolarSystem->solarSystemID()) {
                        $startingPosition = $mapSystem->mapPosition();                        
                    }
                }    
            }            
        }
        
        //check positions
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [0,0])) AND $aroundSolarSystem==null) {
            return self::summMapPositions($startingPosition, [0,0]);
        }else
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [$offsetX,0]))) { //right
            return self::summMapPositions($startingPosition, [$offsetX,0]);
        }else
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [$offsetX,-$offsetY]))) { //downright
            return self::summMapPositions($startingPosition, [$offsetX,-$offsetY]);
        }else
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [$offsetX,+$offsetY]))) { //topright
            return self::summMapPositions($startingPosition, [$offsetX,+$offsetY]);
        }else
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [0,-$offsetY]))) { //down
            return self::summMapPositions($startingPosition, [0,-$offsetY]);
        }else
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [0,+$offsetY]))) { //top
            return self::summMapPositions($startingPosition, [0,+$offsetY]);
        }else
        
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [-$offsetX,0]))) { //left
            return self::summMapPositions($startingPosition, [-$offsetX,0]);
        }else
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [-$offsetX,-$offsetY]))) { //downleft
            return self::summMapPositions($startingPosition, [-$offsetX,-$offsetY]);
        }else
        if (self::isPositionFree($mapSystems, self::summMapPositions($startingPosition, [-$offsetX,+$offsetY]))) { //topleft
            return self::summMapPositions($startingPosition, [-$offsetX,+$offsetY]);
        }else{ 
            //no empty position found, use default (+half offset)            
            return self::summMapPositions($startingPosition, [round($offsetX/2),round($offsetY/2)]);
        }
    }
    
    /**
     * adds up to mapPositions
     * @param array $position1
     * @param array $position2
     * @return array
     */
    protected static function summMapPositions($position1,$position2) {
        return [$position1[0]+$position2[0],$position1[1]+$position2[1]];
    }
    
    /**
     * compares two mapPositions
     * @param array $position1
     * @param array $position2
     * @return boolean
     */
    protected static function isMapPositionEqual($position1,$position2) {
        if ($position1[0]==$position2[0] AND $position1[1]==$position2[1]) {
            return true;
        }
        return false;
    }
    
    /**
     * checks if any of the supplied mapsystems are at the position
     * @param array<\Model\MapSystem> $mapSystems
     * @param array $mapPosition
     * @return boolean
     */
    protected static function isPositionFree($mapSystems, $mapPosition) {
        $free=true;
        foreach($mapSystems as $mapSystem) {            
            if (self::isMapPositionEqual($mapPosition, $mapSystem->mapPosition())) {
                $free=false;
                break;
            }
        }
        return $free;
    }
    
    /**
     * send the tab and all it's contents to the client
     * @param integer $tabID
     * @return boolean
     */
    public static function pushTabToClient(\Model\Tab $tab) {        
        if ($tab===false) {
            return false;
        }
        $tabID = $tab->tabID();
        //tell client to oben a new tab
        self::sendClientOpenTab($tab);
        
        //if run in the igb, inform other clients in the tabs group about pilot location
        if (\Controller\SessionController::session()->sessionType()==2) {
            $pilot = new \Model\Pilot(\Lib\IgbApi::getHeaderCharname(), \Lib\IgbApi::getHeaderShipTypeName(), \Lib\IgbApi::getHeaderSolarSystemID());           
            
            \Controller\PipeController::sendGroupMessage($tab->groupID(),'addPilot',$pilot->returnAssoc());
        }
        
        //get poi cache
        $cachedPois = \Controller\PoiController::getCache($tabID);
        
        $poiResult= null;
        
        //push mapsystems
        $mapSystems = $tab->returnMapSystems();
        foreach($mapSystems as $mapSystem) {
            //send system
            self::sendClientAddSystem($mapSystem); 
            
            //get pois of that system
            //check if we have system pois in cache
            $cached = false;
            if ($cachedPois!==false) {
                foreach ($cachedPois as $poi) {
                    if ($poi->sourceSolarSystemID()==$mapSystem->solarSystemID()) {
                        //yep, we have them ->return them
                        $cached = true;
                        self::sendClientAddPoi($poi);                        
                    }
                }
            }
            //nope it's not in cache->get them
            if ($cached===false) {
                if ($poiResult===null) { 
                    //only load this once. it will stay the same for all other systems
                    $poiResult = \Controller\PoiController::returnPoiResult($tabID);
                }
                $pois = $mapSystem->returnPois($poiResult); //supply method with poiResult to minimize database accesses
                foreach($pois as $poi) {
                    self::sendClientAddPoi($poi);
                }
            }            
        }
        
        //push connections
        $connections = $tab->returnConnections();
        foreach($connections as $connection) {
            self::sendCliendAddConnection($connection);
        }
        
        //push Intel
        $intels = $tab->returnIntels();
        foreach($intels as $intel) {
            self::sendClientAddIntel($intel);
        }
        
        //push Signatures
        $signatures = $tab->returnSignatures();
        foreach($signatures as $signature) {
            self::sendClientSignature($signature);
        }
        
        //push pilots
        $pilots = $tab->returnPilots();
        foreach($pilots as $pilot) {
            self::sendClientPilot($pilot);
        }
    }
    
    /**
     * opens and returns a Tab
     * @param integer $tabID
     * @return \Model\Tab
     */
    public static function returnOpenTab($tabID) {
        //check if we have the tab already opened
        $tab = self::returnTab($tabID);
        if ($tab!==false) {
            return $tab;
        }
        
        //check if we are allowed to open the tab
        if (!\Controller\ClientController::isTabAllowed($tabID)) {
            return false;
        }
        
        //open the tab
        $tab = new \Model\Tab($tabID);
        self:$tabs[] = $tab;
        
        return $tab;
    }
    
    
    public static function unloadPilots() {        
        $prefix = \Db\Sql::dbPrefix();
        
        $access = time() -  \Server::get('conf_pilotlogoff');
        $query = 'SELECT s.characterName,s.solarSystemID,s.shipTypeName,g.groupID FROM '
                . ''.$prefix.'session s, '.$prefix.'groupmember g '
                . 'WHERE s.access <:access';
        $result = \Db\Sql::query($query,[
            ':access'=>(integer)$access
        ]);
        $query = 'DELETE FROM '.$prefix.'session WHERE access<:access';
        \Db\Sql::execute($query,[
            ':access'=>(integer)$access
        ]);
        
        foreach ($result as $row) {
            $pilot = new \Model\Pilot($row['characterName'], $row['shipTypeName'], $row['solarSystemID']);
            \Controller\PipeController::sendGroupMessage($row['groupID'], 'deletePilot', $pilot->returnAssoc());
        }
        
        return true;
    }
    
    public static function rename(\Model\Tab $tab,$tabName) {
        $tab->tabName($tabName);
        $tab->save();
        self::sendPipeGroupTabRename($tab);
    }
    
    public static function delete(\Model\Tab $tab) {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'DELETE FROM '.$prefix.'mapsystem WHERE tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[':tabID'=>$tab->tabID()]);
        
        $query = 'DELETE FROM '.$prefix.'connection WHERE tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[':tabID'=>$tab->tabID()]);
        
        $query = 'DELETE FROM '.$prefix.'tab WHERE tabID = :tabID';
        $rowCount = \Db\Sql::execute($query,[':tabID'=>$tab->tabID()]);
        
        self::sendPipeGroupTabDelete($tab);
    }
    
    /**
     * returns a Tab that's already opened
     * @param integer $tabID
     * @return \Model\Tab
     */
    protected static function returnTab($tabID) {
        foreach(self::$tabs as $tab) {
            if ($tab->tabID()==$tabID) {
                return $tab;
            }
        }
        return false;
    }
    
    protected static function sendClientAddSystem(\Model\MapSystem $mapsystem) {        
        \Server::sendClientAction('addSystem',$mapsystem->returnAssoc());
    }
    
    protected static function sendClientOpenTab(\Model\Tab $tab) {
        \Server::sendClientAction('openTab', $tab->returnAssoc());
    }
    
    protected static function sendCliendAddConnection(\Model\Connection $connection) {
        \Server::sendClientAction('addConnection', $connection->returnAssoc());
    }
    
    protected static function sendClientAddIntel(\Model\Intel $intel) {
        \Server::sendClientAction('addIntel', $intel->returnAssoc());
    }
    
    protected static function sendClientAddPoi(\Model\Poi $poi) {                
        \Server::sendClientAction('addPoi',$poi->returnAssoc());
    }
    
    protected static function sendClientSignature(\Model\Signature $signature) {
        \Server::sendClientAction('addSig',$signature->returnAssoc());
    }
    
    protected static function sendClientPilot(\Model\Pilot $pilot) {
        \Server::sendClientAction('addPilot',$pilot->returnAssoc());
    }
    
    protected static function sendPipeTabAddSystem(\Model\MapSystem $mapsystem,$tabID) {
        \Controller\PipeController::sendTabMessage($tabID, 'addSystem', $mapsystem->returnAssoc());
    }
    
    protected static function sendPipeTabAddIntel(\Model\Intel $intel,$tabID) {
        \Controller\PipeController::sendTabMessage($tabID, 'addIntel', $intel->returnAssoc());
    }
    
    protected static function sendPipeTabAddPoi(\Model\Poi $poi,$tabID) {
        \Controller\PipeController::sendTabMessage($tabID, 'addPoi', $poi->returnAssoc());
    }
    
    protected static function sendPipeTabAddSignature(\Model\Signature $signature,$tabID) {
        \Controller\PipeController::sendTabMessage($tabID, 'addSig', $signature->returnAssoc());
    }
    
    protected static function sendPipeTabAddConnection(\Model\Connection $connection,$tabID) {
        \Controller\PipeController::sendTabMessage($tabID, 'addConnection', $connection->returnAssoc());
    }
    
    protected static function sendPipeGroupTabRename(\Model\Tab $tab) {
        \Controller\PipeController::sendGroupMessage($tab->groupID(), 'renameTab', $tab->returnAssoc());
    }
    
    protected static function sendPipeGroupTabDelete(\Model\Tab $tab) {
        \Controller\PipeController::sendGroupMessage($tab->groupID(), 'deleteTab', $tab->returnAssoc());
    }
}
