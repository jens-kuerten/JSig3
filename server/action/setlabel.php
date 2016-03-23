<?php

namespace Action;
class SetLabel extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        
        self::$data = $data;
        $solarSystemID = self::returnDataVariable('solarSystemID');
        $tabID = self::returnDataVariable('tabID');
        $label = self::returnDataVariable('label');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==false) {
            return false;
        }
        
        \Controller\MapSystemController::setLabel($solarSystemID, $tabID, $label);
        return true;
    }
}
