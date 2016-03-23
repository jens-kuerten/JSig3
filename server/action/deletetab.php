<?php


namespace Action;
class DeleteTab extends BaseAction{
    public static function Execute($data) {
        self::requireLogin();
        
        self::$data = $data;        
        $tabID = self::returnDataVariable('tabID');                
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==false) {            
            return false;
        }
        \Controller\TabController::delete($tab);
        return true;
    }
}
