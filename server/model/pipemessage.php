<?php


namespace Model;
class PipeMessage extends BaseModel {
    protected $tableName = 'pipe';
    protected $assoc =[];
    
    public function __construct($param=null) {
        if (is_array($param)) {
            $this->loadFromAssoc($param);
        }
    }
   
    /**
     * returns or sets the tabID
     * @param integer $tabID
     * @return integer
     */
    public function tabID($tabID = null) {
        if ($tabID!== null) {
            $this->assoc['tabID']=$tabID;
            return true;
        }else{
            return $this->returnProperty('tabID');
        }
    }
    
    /**
     * returns or sets the groupID
     * @param integer $groupID
     * @return integer
     */
    public function groupID($groupID = null) {
        if ($groupID!== null) {
            $this->assoc['groupID']=$groupID;
            return true;
        }else{
            return $this->returnProperty('groupID');
        }
    }
    
    /**
     * returns or sets the action
     * @param string $action
     * @return string
     */
    public function action($action = null) {
        if ($action!== null) {
            $this->assoc['action']=$action;
            return true;
        }else{
            return $this->returnProperty('action');
        }
    }
    
    /**
     * returns or sets the data
     * @param array $data
     * @return array
     */
    public function data($data = null) {
        if ($data!== null) {
            $this->assoc['data']=  json_encode($data);
            return true;
        }else{         
            return json_decode($this->returnProperty('data'),true);
        }
    }
    
    /**
     * returns or sets the creationTime
     * @param integer $creationTime
     * @return integer
     */
    public function creationTime($creationTime = null) {
        if ($creationTime!== null) {
            $this->assoc['creationTime']=$creationTime;
            return true;
        }else{
            return $this->returnProperty('creationTime');
        }
    }
    
    /**
     * returns the message ID
     * @return integer
     */
    public function messageID() {
        return $this->returnProperty('messageID');
    }
    
    public function save() {
        $this->assoc['messageID'] = $this->writeToDb(true);        
        return $this->assoc['messageID'];
    }
}
