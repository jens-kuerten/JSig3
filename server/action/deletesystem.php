<?php



namespace Action;
class DeleteSystem extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;        
        $solarSystemID = self::returnDataVariable('solarSystemID');
        $tabID = self::returnDataVariable('tabID');
        
        $tab = self::hasTabAccess($tabID);        
        if ($tab===false) {
            //user isn't in the required group for this Tab
            return false;
        }
        
        \Controller\MapSystemController::deleteSolarSystem($solarSystemID, $tabID);
        
    }
}
