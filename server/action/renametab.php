<?php


namespace Action;
class RenameTab extends BaseAction{
    public static function Execute($data) {
        self::requireLogin();
        
        self::$data = $data;        
        $tabID = self::returnDataVariable('tabID');        
        $tabName = self::returnDataVariable('tabName');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==false) {            
            return false;
        }
        \Controller\TabController::rename($tab, $tabName);
        return true;
    }
}
