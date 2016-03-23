<?php

namespace Model;

class Client extends BaseModel{
    protected $groups = [];    
    protected $groupsLoaded = false;
    
    protected $allowedTabs;
    
    protected $sessionID = '';
    protected $assoc = [
        
    ];
    
    protected $tableName = 'account';
    
    
    
    /**
     * 
     * @param string $param username or accountID
     * @param boolean $loadByUserName true to load by username
     */
    public function __construct($param = null,$loadByUserName = false) {
        if ($loadByUserName===true) {
            $this->loadByUserName($param);
        }else if($param!==null) {            
            $this->loadByAccountID($param);
        }
    }
    
    public function passwordHash($passwordHash=null) {
        if ($passwordHash!==null) {
            $this->assoc['passwordHash']=$passwordHash;
            return true;
        }else{
            return $this->returnProperty('passwordHash');
        }
    }
    
    public function mainCharacterName($characterName = null) {
        if ($characterName!=null) {
            $this->assoc['mainCharacterName'] = $characterName;
        }else {
            return $this->returnProperty('mainCharacterName');
        }
        
    }
    public function userName($username = null) {
        if ($username!=null) {
            $this->assoc['username'] = $username;
        }else {
            return $this->returnProperty('username');
        }
        
    }
    
    public function accountID() {
        return $this->returnProperty('accountID');
    }
    
    /**
     * returns an array of groups that the account is part of
     * @return array<\Model\Group>
     */
    public function returnGroups() {
        if (!$this->groupsLoaded) {            
            $this->groupsLoaded = $this->loadGroups();
        }
        if ($this->groupsLoaded) {
            return $this->groups;
        }else{
            Throw new Exception('can\'t load groups, user not logged in');
        }        
    }
    
    /**
     * returns an array of allowed tabs
     * @return array<\Model\Tab>
     */
    public function returnAllowedTabs() {
        if ($this->allowedTabs===null) {
            $this->loadAllowedTabs();
        }
        
        return $this->allowedTabs;
    }
    
    /**
     * returns or sets an array with of tabIDs
     * @param array $openTabIDs
     * @return array
     */
    public function openTabIDs($openTabIDs = null) {
        if ($openTabIDs!==null) {
            $this->assoc['openTabIDs']=  implode(',', $openTabIDs);
        }else{
            $ids = $this->returnProperty('openTabIDs');
            if ($ids === null) {
                $this->loadByAccountID($this->accountID());
            }
            $ids = $this->returnProperty('openTabIDs');            
            if ($ids != null) {
                return explode(',', $ids);
            }
            return [];
        }
    }
    
    protected function loadByUserName($username) {
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        $query = 'SELECT * FROM '.$prefix.$tableName.' WHERE username =:username';
        $row = \Db\Sql::queryRow($query,[
                ':username' =>$username
            ]);
        if (!empty($row)) {
            $this->assoc = array_merge($this->assoc,$row);
            return true;
        }
        return false;
    }
    
    protected function loadByAccountID($accountID) {
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        $query = 'SELECT * FROM '.$prefix.$tableName.' WHERE accountID =:accountID';
        $row = \Db\Sql::queryRow($query,[
                ':accountID' =>$accountID
            ]);
        if (!empty($row)) {
            $this->assoc = array_merge($this->assoc,$row);
            return true;
        }
        return false;            
    } 
    
    /**
     * loads groups from AccountID
     */
    protected function loadGroups() {
        $prefix = \Db\Sql::dbPrefix();
        $accountID = \Controller\SessionController::session()->accountID();
        if ($accountID===null) {
            return false;
        }
        
        $query = 'SELECT g.* FROM '.$prefix.'group as g, '.$prefix.'groupmember as m '
                . 'WHERE g.groupID = m.groupID AND m.accountID = :accountID';
        $result = \Db\Sql::query($query,[
            ':accountID'    =>  $accountID
        ]);
        $this->groups = []; //make sure array is empty
        if (!empty($result)) {
            foreach($result as $groupAssoc) {
                $group = new \Model\Group($groupAssoc);
                $this->groups[] = $group;
            }
        }
        return true;
    }
    
    protected function loadAllowedTabs() {                
        $prefix = \Db\Sql::dbPrefix();        
        
        $accountID = \Controller\SessionController::session()->accountID();
        $query = 'SELECT t.* FROM '.$prefix.'tab as t, '.$prefix.'groupmember as m '
                . 'WHERE t.groupID = m.groupID AND m.accountID = :accountID';
        $result = \Db\Sql::query($query,[
            ':accountID'=>$accountID
        ]);
        
        if (!empty($result)) {
            foreach($result as $tabAssoc) {
                $this->allowedTabs[] = new \Model\Tab($tabAssoc);
            }
        }
        return true;
    }
}
