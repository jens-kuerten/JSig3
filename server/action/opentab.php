<?php


namespace Action;
class OpenTab extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();;
        
        self::$data = $data;
        $tabID = self::returnDataVariable('tabID');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==null) {
            return false;
        }
        
        if (\Controller\ClientController::openTab($tabID)) { //add tab to list of open tab, then push it to the client
            \Controller\TabController::pushTabToClient($tab);            
            return true;
        }
        
        return false;
        
    }
}
