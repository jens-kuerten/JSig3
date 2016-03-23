<?php


namespace Action;
class CloseTab extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();;
        
        self::$data = $data;
        $tabID = self::returnDataVariable('tabID');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==null) {
            return false;
        }
        \Controller\ClientController::closeTab($tabID);
        self::sendClientAction('closeTab', ['tabID'=>$tabID]);
        return true;        
        
        return false;
        
    }
}
