<?php


namespace Action;

class CopyChain extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $fromTabID = self::returnDataVariable('fromTabID');
        $toTabID = self::returnDataVariable('toTabID');
        $solarSystemIDs = self::returnDataVariable('solarSystemIDs');
        
        $deleteSource = self::returnDataVariable('deleteSource');
        
        $fromTab = self::hasTabAccess($fromTabID);
        if ($fromTab==false) {
            return false;
        }
        
        $toTab = self::hasTabAccess($toTabID);
        if ($toTab==false) {
            return false;
        }
        
        \Controller\TabController::CopyChain($fromTab, $toTab, $solarSystemIDs,$deleteSource);
        
        
    }
}
