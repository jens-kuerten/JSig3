<?php


namespace Action;

abstract class BaseAction {
    protected static $data = [];
    
    public static function Execute($param) {
        
    }        

    /**
     * returns a variable from the message data, $null if not set
     * @param string $key
     * @param mixed $null
     * @return string
     */
    protected static function returnDataVariable($key,$null = null) {
        if (isset(self::$data[$key])) {            
            return json_decode(htmlspecialchars(json_encode(self::$data[$key]),ENT_NOQUOTES),true);
        }else{            
            return $null;
        }            
    }
    protected static function requireLogin() {
        $sessionType = \Controller\SessionController::session()->sessionType();
        if ($sessionType>0) {
            return true;
        }else{
            trigger_error('not logged in');
        }
    }
    
    /**
     * returns the Tab if user has access, false if not
     * @param integer $tabID
     * @return \Model\Tab
     */
    protected static function hasTabAccess($tabID) {
        $allowedTabs = \Controller\ClientController::returnAllowedTabs();
        foreach ($allowedTabs as $tab) {
            if ($tab->tabID() == $tabID) {
                return $tab;
            }
        }
        return false;
    }
    
    protected static function sendClientAction($action,$data) {
        \Server::sendClientAction($action, $data);
    }
}
