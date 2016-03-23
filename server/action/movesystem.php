<?php


namespace Action;
class MoveSystem extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        
        self::$data = $data;
        $solarSystemID = self::returnDataVariable('solarSystemID');
        $tabID = self::returnDataVariable('tabID');
        $mapX = self::returnDataVariable('mapX');
        $mapY = self::returnDataVariable('mapY');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==false) {
            return false;
        }
        
        \Controller\MapSystemController::moveSolarsystem($solarSystemID, $tabID, [$mapX,$mapY]);
        return true;
    }
}
