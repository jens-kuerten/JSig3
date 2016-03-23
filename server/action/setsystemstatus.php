<?php


namespace Action;
class SetSystemStatus extends BaseAction{
    public static function Execute($data) {
        self::requireLogin();
        
        self::$data = $data;
        $solarSystemID = self::returnDataVariable('solarSystemID');
        $tabID = self::returnDataVariable('tabID');
        $systemStatus = self::returnDataVariable('systemStatus');
        $state = self::returnDataVariable('state');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==false) {
            return false;
        }
        
        if ($systemStatus==='home') {
            \Controller\MapSystemController::setHome($solarSystemID, $tabID, $state);
        }else if($systemStatus==='pin') {
            \Controller\MapSystemController::setLocked($solarSystemID, $tabID, $state);
        }else if ($systemStatus==='rallypoint') {
            \Controller\MapSystemController::setRallypoint($solarSystemID, $tabID, $state);
        }
    }
}
