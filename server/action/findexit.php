<?php



namespace Action;

class FindExit extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $solarSystemName = self::returnDataVariable('solarSystemName');
        $tabID = self::returnDataVariable('tabID');
        
        $tab = self::hasTabAccess($tabID);
        if ($tab==false) {
            echo $tabID;
            return false;
        }
        $targetSystem = new \Model\MapSystem($solarSystemName);
        $solarSystemID = $targetSystem->solarSystemID();
        
        $route = new \Lib\Route($solarSystemID,false);
        $routeSafe = new \Lib\Route($solarSystemID,true);
        $exits = [];
        
        //get systems on the tab
        $tabSystems = $tab->returnMapSystems();
        
        foreach ($tabSystems as $system) {
            $mapSystemClass = $system->solarSystemClass();
            if ($mapSystemClass==7 OR $mapSystemClass==8 or $mapSystemClass==9 ) {                
                $exits[] = [
                    'solarSystemName'=>$system->solarSystemName(),
                    'j'=>$route->jumps($system->solarSystemID()),
                    'js'=>$routeSafe->jumps($system->solarSystemID()),
                    'ly'=>$targetSystem->calculateDistance($system->evePosition())
                        ];
            }
        }
        \Server::sendClientAction('foundexits', $exits);
    }
}
