<?php

namespace Model;
class Intel extends BaseModel{
    protected $tableName ='intel';
    protected $assoc = [];
    
    public function __construct($param = null) {
        if (is_array($param)) {
            $this->loadFromAssoc($param);
        }else if (is_numeric($param)){
            if (!$this->loadFromDb($param)) {
                trigger_error('couldn\'t find intelid');
            }
        }
    }
    
    public function save() {
        $this->assoc['id'] = $this->writeToDb(true);
        return $this->returnProperty('id');
    }
    
    public function delete() {
        $id = $this->returnProperty('id');
        if ($id===null) {
            return false;
        }
        $accountID = \Controller\SessionController::session()->accountID();
        $prefix = \Db\Sql::dbPrefix();                
        //delete where id matches and the groupID belongs to a group the account is a member of
        $query ='DELETE i FROM '.$prefix.'intel i, '.$prefix.'groupmember m '
                . 'WHERE i.id = :id AND i.groupID=m.groupID AND m.accountID = :accountID';
        $rowCount = \Db\Sql::execute($query,[
            ':id'=>$id,
            ':accountID'=>$accountID
        ]);
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
    
    /**
     * returns or sets the groupID
     * @param integer $groupID
     * @return integer
     */
    public function groupID($groupID = null) {
        if ($groupID!==null) {
            $this->assoc['groupID']=$groupID;
            return true;
        }else{
            return $this->returnProperty('groupID');
        }
    }
    
    /**
     * returns or sets the solarSystemID
     * @param integer $solarSystemID
     * @return integer
     */
    public function solarSystemID($solarSystemID = null) {
        if ($solarSystemID!==null) {
            $this->assoc['solarSystemID']=$solarSystemID;
            return true;
        }else{
            return $this->returnProperty('solarSystemID');
        }
    }
    
    /**
     * returns or sets the type
     * @param string $type
     * @return string
     */
    public function type($type = null) {
        if ($type!==null) {
            $this->assoc['type']=$type;
            return true;
        }else{
            return $this->returnProperty('type');
        }
    }
    
    /**
     * returns or sets the id
     * @param integer $id
     * @return integer
     */
    public function id($id = null) {
        if ($id!==null) {
            $this->assoc['id']=$id;
            return true;
        }else{
            return $this->returnProperty('id');
        }
    }
    
    /**
     * returns or sets the value
     * @param string $value
     * @return string
     */
    public function value($value = null) {
        if ($value!==null) {
            $this->assoc['value']=$value;
            return true;
        }else{
            return $this->returnProperty('value');
        }
    }
    
    /**
     * returns or sets the eveID
     * @param integer $eveID
     * @return integer
     */
    public function eveID($eveID = null) {
        if ($eveID!==null) {
            $this->assoc['eveID']=$eveID;
            return true;
        }else{
            return $this->returnProperty('eveID');
        }
    }
    
    /**
     * returns or sets the creationTime
     * @param integer $creationTime
     * @return integer
     */
    public function creationTime($creationTime = null) {
        if ($creationTime!==null) {
            $this->assoc['creationTime']=$creationTime;
            return true;
        }else{
            return $this->returnProperty('creationTime');
        }
    }
    
    
    protected function loadFromDb($id) {
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        
        $query = 'SELECT * FROM '.$prefix.$tableName.' WHERE id = :id';
        $row = \Db\Sql::queryRow($query,[
            ':id'=>$id
        ]);
        if (!empty($row)) {
            $this->loadFromAssoc($row);
            return true;
        }
        return false;
    }
}
