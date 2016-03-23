<?php


namespace Controller;

class ClientController extends BaseController {

    /**
     *
     * @var \Model\Client 
     */
    protected static $client;

    /**
     * verifies the login credentials and sets session if login successfull
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public static function checkLogin($username, $password) {
        self::$client = new \Model\Client($username, true);
        $passwordHash = self::$client->passwordHash();
        if ($passwordHash === null) {
            return false;
        }
        if (password_verify($password, $passwordHash)) {
            self::loginSession();
            return true;
        }
    }
    
    public static function checkToken($token) {
        $prefix = \Db\Sql::dbPrefix();
        $expiretime = time()-(86400 * 30); //30 days
        $query = 'SELECT a.username FROM '.$prefix.'account as a, '.$prefix.'token as t WHERE '
                . 't.accountID = a.accountID AND t.token = :token AND t.timeCreated >:expiretime';
        $row = \Db\Sql::queryRow($query,[
            ':expiretime'=>$expiretime,
            ':token'=>$token
                ]);
        if (!empty($row)) {
            $username = $row['username'];
            self::$client = new \Model\Client($username, true);
            self::loginSession();
            $accountID = \Controller\SessionController::session()->accountID();
            $newtoken = self::updateToken($token, $accountID);
            setcookie('JSIG3Token', $newtoken, time() + (86400 * 30), "/"); // 86400 = 1 day            
            return true;
        }
        return false;
    }

    /**
     * returns true if client is running the IGB and trusted the page
     * @return boolean
     */
    public static function isTrusted() {
        if (\Lib\IgbApi::getHeaderCharname() != false AND
                \Lib\IgbApi::getHeaderSolarSystemID() != false AND
                \Lib\IgbApi::getHeaderShipTypeName() != false) {            
            return true;
        }
        return false;
    }

    /**
     * returns an array of groups that the account is part of
     * @return array<\Model\Group>
     */
    public static function returnGroups() {
        if (self::$client === null) {
            self::$client = new \Model\Client(\Controller\SessionController::session()->accountID());
        }
        return self::$client->returnGroups();
    }

    /**
     * returns an array of TabIDs that the client has opened
     * @return array
     */
    public static function returnOpenTabIDs() {
        if (self::$client === null) {
            self::$client = new \Model\Client(\Controller\SessionController::session()->accountID());
        }
        return self::$client->openTabIDs();
    }

    /**
     * returns an array if allowed tabs
     * @return array<\Model\Tab>
     */
    public static function returnAllowedTabs() {
        if (self::$client === null) {
            self::$client = new \Model\Client(\Controller\SessionController::session()->accountID());
        }
        return self::$client->returnAllowedTabs();
    }

    /**
     * checks if client is allowed to access the tab
     * @param integer $tabID
     * @return boolean
     */
    public static function isTabAllowed($tabID) {
        $allowedTabs = self::returnAllowedTabs();
        if (empty($allowedTabs)) {
            return false;
        }
        foreach ($allowedTabs as $tab) {
            if ($tab->tabID() == $tabID) {
                return true;
            }
        }
        return false;
    }

    /**
     * adds a tab to the list of open tabs
     * @param integer $tabID
     */
    public static function openTab($tabID) {
        $openTabIDs = self::returnOpenTabIDs();
        if (!in_array($tabID, $openTabIDs)) {
            $openTabIDs[] = $tabID;
            self::$client->openTabIDs($openTabIDs);
            self::$client->save();
            return true;
        }
        return false;
    }
    
    /**
     * removes a tab from the list of open tabs
     * @param integer $tabID
     * @return boolean
     */
    public static function closeTab($tabID) {
        $openTabIDs = self::returnOpenTabIDs();
        $index = array_search($tabID, $openTabIDs);
        if ($index !== false) {
            unset($openTabIDs[$index]);
            self::$client->openTabIDs($openTabIDs);
            self::$client->save();
            return true;
        }
        return false;
    }
    
    /**
     * returns true if the solarsystemID in the header is different than in the session
     * @return boolean
     */
    public static function hasJumped() {
        $session = \Controller\SessionController::session();
        //check if we are running in the ingamebrowser
         if ($session->sessionType()==2) {
             if ($session->solarSystemID()!= \Lib\IgbApi::getHeaderSolarSystemID()) {
                 return true;
             }
         }
         return false;
    }
    
    /**
     * returns true if the shiptypename in the header is different than in the session
     * @return boolean
     */
    public static function hasChangedShipType() {
        $session = \Controller\SessionController::session();
        //check if we are running in the ingamebrowser
         if ($session->sessionType()==2) {
             if ($session->shipTypeName()!= \Lib\IgbApi::getHeaderShipTypeName()) {
                 return true;
             }
         }
         return false;
    }
    
    /**
     * returns a tab if we are allowed to access it, null if no
     * @param integer $tabID
     * @return \Model\Tab
     */
    public static function returnAllowedTab($tabID) {
        $allowedTabs = \Controller\ClientController::returnAllowedTabs();
        foreach ($allowedTabs as $tab) {
            if ($tab->tabID() == $tabID) {
                return $tab;
            }
        }
        return null;
    }
    
    /**
     * sends information about the current clients pilot location to other clients
     * @param \Model\Pilot $pilot
     */
    public static function pushPilotToPipe(\Model\Pilot $pilot) {
        //get groups of opened tabs
        $tabIDs = self::returnOpenTabIDs();
        $allowedTabs = self::returnAllowedTabs();
        $groupIDs = [];
        foreach ($allowedTabs as $tab) {
            if (in_array($tab->tabID(), $tabIDs) AND !  in_array($tab->tabID(), $groupIDs)) {
                \Controller\PipeController::sendGroupMessage($tab->groupID(),'addPilot',$pilot->returnAssoc());
                $groupIDs[] = $tab->groupID(); //only send message once for each group;
            }
        }
        self::sendPilotSolarSystemID();
    }
    
    public static function sendPilotSolarSystemID() {
        \Server::sendClientAction('spssi', ['solarSystemID'=> \Controller\SessionController::session()->solarSystemID()]);
    }
    public static function updateGroups() {
        $groups = self::returnGroups();
        $characters = self::returnCharacters();
        $accountID = \Controller\SessionController::session()->accountID();        
        //delete alliance and corp groups that have no corresponding character
        foreach ($groups as $group) {
            $found = false;            
            if ($group->type() === 'corporation') {
                foreach ($characters as $character) {
                    if ($character->corporationID()==$group->eveID()) {
                        $found = true;
                    }
                }
            }else if($group->type() === 'alliance') {
                foreach ($characters as $character) {
                    if ($character->allianceID()==$group->eveID()) {
                        $found = true;
                    }
                }
            }else {                
                $found = true;
            }
            
            if ($found == false) {                
                $group->delete($accountID);
            }
        }
        
        //create groups that are missing
        $addedGroups = [];
        foreach ($characters as $character) {
            $foundAlly = false;
            $foundCorp=false;
            foreach($groups as $group) {
                if ($group->type()==='corporation' AND $group->eveID() == $character->corporationID()) {
                    $foundCorp = true;
                }else
                if ($group->type()==='alliance' AND $group->eveID() == $character->allianceID()) {
                    $foundAlly = true;
                }
            }
            if ($character->allianceID()==0) {
                $foundAlly=true;
            }
            if ($character->corporationID()==0) {
                $foundCorp=true;
            }
            
            if ($foundAlly==false) {
                $allyGroupID = \Controller\GroupController::getAllyGroupID($character->allianceName(), $character->allianceID());
                self::joinGroup($allyGroupID);
            }
            if ($foundCorp==false) {
                $corpGroupID = \Controller\GroupController::getCorpGroupID($character->corporationName(), $character->corporationID());
                self::joinGroup($corpGroupID);
            }
        }        
    }
    
    protected static function joinGroup($groupID) {
        if ($groupID==0) {
            return false;
        }
        $prefix = \Db\Sql::dbPrefix();
        $query = 'INSERT IGNORE INTO '.$prefix.'groupmember (accountID,groupID) VALUES (:accountID,:groupID) ';
        $rowCount = \Db\Sql::execute($query,[
            ':accountID'=> \Controller\SessionController::session()->accountID(),
            ':groupID'=>$groupID
        ]);
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
    
    public static function deleteCharacter($characterID,$accountID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'DELETE FROM '.$prefix.'character WHERE characterID = :characterID AND accountID = :accountID';
        $rowCount = \Db\Sql::execute($query,[
            ':characterID'=>$characterID,
            ':accountID'=>$accountID
        ]);
        
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
    
    public static function returnCharacters() {
        $characters = array();
        $accountID = \Controller\SessionController::session()->accountID();
        $prefix = \Db\Sql::dbPrefix();        
        $query = 'SELECT characterID FROM '.$prefix.'character WHERE accountID = :accountID';
        $result = \Db\Sql::query($query,[':accountID'=>$accountID]);        
        if (!empty($result)) {
            foreach ($result as $row) {
                $characters[] = new \Model\eveCharacter($row['characterID']);
            }
        }
        return $characters;
    }
    /**
     * hashes the password
     * @param string $password
     * @return string
     */
    protected static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * sets session variables; called after successfull login
     */
    protected static function loginSession() {        
        $session = \Controller\SessionController::session();
        $session->characterName(self::$client->userName());
        $session->accountID(self::$client->accountID());
        $session->sessionType(1);
        if (self::isTrusted()) {
            $session->characterName(\Lib\IgbApi::getHeaderCharname());
            $session->shipTypeName(\Lib\IgbApi::getHeaderShipTypeName());
            $session->solarSystemID(\Lib\IgbApi::getHeaderSolarSystemID());
            $session->sessionType(2);            
        }
        self::updateGroups();
    }
    
     protected static function returnNewToken() {
        $secret = time();
        for ($i = 0; $i < 20; ++$i) {
            $secret .= chr(mt_rand(0, 255));
        }
        return md5($secret);
    }
        
    public static function updateToken($oldtoken,$accountID) {        
        $dbPrefix = \Db\Sql::dbPrefix();        
        
        $token = self::returnNewToken();
        $query = 'UPDATE '.$dbPrefix.'token SET token=:token , timeCreated=:time WHERE accountID = :accountid AND token=:oldtoken';
        $rowCount = \Db\Sql::execute($query,array(
            ":oldtoken"=> $oldtoken,
            ":token"=>  $token,
            ":time"=>time(),
            ":accountid"=> $accountID
        ));        
        if ($rowCount>0) {
            return $token;
        }
        return false;
    }
    
    
    
}
