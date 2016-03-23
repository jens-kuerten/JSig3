<?php


namespace Action;
class FindTabConnection extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $fromTabID = self::returnDataVariable('fromTabID');
        $toTabID = self::returnDataVariable('toTabID');
        
        $fromTab = self::hasTabAccess($fromTabID);
        if ($fromTab==false) {
            return false;
        }
        
        $toTab = self::hasTabAccess($toTabID);
        if ($toTab==false) {
            return false;
        }
        
        $connections = \Controller\RouteController::findTabConnections($fromTab, $toTab);
        $reply = [
            'fromTabName'=>$fromTab->tabName(),
            'toTabName'=>$toTab->tabName(),
            'connections'=>$connections
                ];
        \Server::sendClientAction('foundTabConnection', $reply);
    }
}
