<?php


namespace Action;
class ConnectionStage extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();;
        
        self::$data = $data;
        $tabID = self::returnDataVariable('tabID');
        $fromSolarSystemID = self::returnDataVariable('fromSolarSystemID');
        $toSolarSystemID = self::returnDataVariable('toSolarSystemID');
        $massStage = self::returnDataVariable('massStage');
        $timeStage = self::returnDataVariable('timeStage');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==null) {
            return false;
        }
        
        if ($massStage !== null) {
            \Controller\ConnectionController::setMassStage($fromSolarSystemID, $toSolarSystemID, $tab, $massStage);
           
        }
        if ($timeStage !== null) {
            \Controller\ConnectionController::setTimeStage($fromSolarSystemID, $toSolarSystemID, $tab, $timeStage);           
        }
        
        return false;
        
    }
}
