<?php


namespace Action;
class AddConnection extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;         
        $fromSolarSystemName = self::returnDataVariable('fromSolarSystemName');
        $toSolarSystemName = self::returnDataVariable('toSolarSystemName');
        $tabID = self::returnDataVariable('tabID');        
        $tab = self::hasTabAccess($tabID);        
        if ($tab===false) {
            //user isn't in the required group for this Tab
            return false;
        }
        
        //load them
        $fromSolarSystem = new \Model\MapSystem($fromSolarSystemName);
        $toSolarSystem = new \Model\MapSystem($toSolarSystemName);        
        if ($fromSolarSystem->solarSystemID()!==null AND $toSolarSystem->solarSystemID()!==null) {
            //both are valid solarsystems -> add a connection between them
            \Controller\TabController::makeConnection($fromSolarSystem, $toSolarSystem, $tab);            
        }else if($fromSolarSystem->solarSystemID()!==null){
            \Controller\TabController::addSystem($fromSolarSystem, $tab);
        }else if($toSolarSystem->solarSystemID()!==null) {
            \Controller\TabController::addSystem($toSolarSystem, $tab);
        }
    }
}
