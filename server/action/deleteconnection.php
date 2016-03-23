<?php


namespace Action;
class DeleteConnection extends BaseAction{
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $fromSolarSystemID = self::returnDataVariable('fromSolarSystemID');
        $toSolarSystemID = self::returnDataVariable('toSolarSystemID');
        $tabID = self::returnDataVariable('tabID');
        
        $tab = self::hasTabAccess($tabID);        
        if ($tab===false) {
            //user isn't in the required group for this Tab
            return false;
        }
        
        \Controller\ConnectionController::deleteConnection($fromSolarSystemID, $toSolarSystemID, $tabID);
    }
}
