<?php

namespace Model;

class Group extends BaseModel{
    protected $tableName = 'group';
    
    protected $assoc = [];
    
    public function __construct($param = null) {
        if (is_array($param)) {
            $this->loadFromAssoc($param);
        }
    }
    
    public function save() {
        $this->assoc['groupID'] = $this->writeToDb(true);
        return $this->returnProperty('groupID');
    }
    
    public function title($title = null) {
        if ($title !== null) {
            $this->assoc['title']=$title;
        }else{
            return $this->returnProperty('title');
        }
    }
    
    public function type($type = null) {
        if ($type !== null) {
            $this->assoc['type']=$type;
        }else{
            return $this->returnProperty('type');
        }
    }
    
    public function eveID($eveID = null) {
        if ($eveID !== null) {
            $this->assoc['eveID']=$eveID;
        }else{
            return $this->returnProperty('eveID');
        }
    }
    
    public function groupID($groupID = null) {
        if ($groupID !== null) {
            $this->assoc['groupID']=$groupID;
        }else{
            return $this->returnProperty('groupID');
        }
    }
    
    public function permissionLevel($permissionLevel = null) {
        if ($permissionLevel!==null) {
            $this->assoc['permissionLevel']=$permissionLevel;
        }else{
            return $this->returnProperty('permissionLevel');
        }
    }
    
    public function hidden($hidden = null) {
        if ($hidden !== null) {
            $this->assoc['hidden']=$hidden;
        }else{
            return $this->returnProperty('hiddden');
        }
    }
    
    public function delete($accountID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'DELETE FROM '.$prefix.'groupmember WHERE accountID = :accountID AND groupID = :groupID';
        $rowCount = \Db\Sql::execute($query,[
            ':accountID'=>$accountID,
            ':groupID'=>$this->groupID()
        ]);
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
        
}
