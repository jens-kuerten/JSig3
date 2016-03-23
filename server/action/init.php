<?php

namespace Action;

class Init extends BaseAction {
    public static function Execute($param) {        
        //this action requires that the user is logged in
        self::requireLogin();        
        
        //inform client which groups user is in
       self::pushGroups();
       //send system effect data to client
       self::pushEffects();
       //send wormhole data to client (max jump masses, etc.)
       self::pushWormholes();
       //inform clients which tabs he can open
       self::pushAllowedTabs();
       
       //unload offline pilots
       \Controller\TabController::unloadPilots();
       
       //push open Tabs to client
       self::pushOpenTabs();
       
       //push pois to client
       self::pushPOIs();
       
       $highesMessageID = \Controller\PipeController::getHighestPipeMessageID();
       \Server::sendClientAction('setMessageID', ['id'=>$highesMessageID]);
       
       if (\Controller\SessionController::session()->sessionType()==2) {
           \Server::sendClientAction('spssi', ['solarSystemID'=> \Lib\IgbApi::getHeaderSolarSystemID()]);
       }       
    }
    
    
    protected static function pushOpenTabs() {
        $openTabIDs = \Controller\ClientController::returnOpenTabIDs();        
        
        foreach($openTabIDs as $openKey=>$openTabID) {
            $tab = \Controller\TabController::returnOpenTab($openTabID);
            if ($tab === false) {
                //couldn't open tab because it doesn't exist or we have no permission
                unset($openTabIDs[$openKey]);
                continue;
            }
            \Controller\TabController::pushTabToClient($tab);           
        }
    }
    
    protected static function pushAllowedTabs() {
        $allowedTabs = \Controller\ClientController::returnAllowedTabs();
        if (!empty($allowedTabs)) {
            $data = [];
            foreach($allowedTabs as $tab) {
                $data[] = $tab->returnAssoc();
            }
            self::sendClientAction('addAllowedTabs',$data);            
        }
    }
    protected static function pushGroups() {
        $groups = \Controller\ClientController::returnGroups();
        if (!empty($groups)) {
            $data = [];
            foreach($groups as $group) {
                $data[] = $group->returnAssoc();
            }
            self::sendClientAction('addGroups',$data);            
        }
    }
    
    protected static function pushEffects() {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT * FROM '.$prefix.'effect ORDER BY isPositive DESC';
        $result = \Db\Sql::query($query);
        
        if(!empty($result)) {
            self::sendClientAction('addEffects', $result);
        }
    }
    
    protected static function pushWormholes() {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT * FROM '.$prefix.'wormhole';
        $result = \Db\Sql::query($query);
        
        if(!empty($result)) {
            self::sendClientAction('addWormholes', $result);
        }
    } 
    
    protected static function pushPOIs() {
        $accountID = \Controller\SessionController::session()->accountID();
        $pois = \Controller\PoiController::returnAccPOIs($accountID);
        foreach ($pois as $poi) {
            self::sendClientAction('addPoi', $poi->returnAssoc());
        }
    }
}