<?php


namespace Action;
class AddIntel extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $tabID = self::returnDataVariable('tabID');
        $solarSystemID = self::returnDataVariable('solarSystemID');
        $type = self::returnDataVariable('type');
        $value = self::returnDataVariable('value');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab!==null) {
            \Controller\IntelController::createIntel($value, $type, $tab->groupID(), $solarSystemID);
        }
    }
}
