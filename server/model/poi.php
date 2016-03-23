<?php

namespace Model;
class Poi extends BaseModel {
    protected $tableName = 'poi';
    protected $assoc = [];
    
    public function __construct($param = null) {
        if (is_array($param)) {
            $this->loadFromAssoc($param);
        }
    }
    
    /**
     * returns or sets the description
     * @param string $description
     * @return String
     */
    public function description($description = null) {
        if ($description!== null) {
            $this->assoc['description'] = $description;
            return true;
        }else{
            return $this->returnProperty('description');
        }
    }
    
     /**
     * returns or sets the type
     * @param string $type
     * @return String
     */
    public function type($type = null) {
        if ($type!== null) {
            $this->assoc['type'] = $type;
            return true;
        }else{
            return $this->returnProperty('type');
        }
    }
    
    /**
     * returns or sets the solarSystemName
     * @param string $solarSystemName
     * @return String
     */
    public function solarSystemName($solarSystemName = null) {
        if ($solarSystemName!== null) {
            $this->assoc['solarSystemName'] = $solarSystemName;
            return true;
        }else{
            return $this->returnProperty('solarSystemName');
        }
    }
    /**
     * returns or sets the maxLy
     * @param integer $maxLy
     * @return integer
     */
    public function maxLy($maxLy = null) {
        if ($maxLy!== null) {
            $this->assoc['maxLy'] = $maxLy;
            return true;
        }else{
            return $this->returnProperty('maxLy');
        }
    }
    
    /**
     * returns or sets the maxJumps
     * @param integer $maxJumps
     * @return integer
     */
    public function maxJumps($maxJumps = null) {
        if ($maxJumps!== null) {
            $this->assoc['maxJumps'] = $maxJumps;
            return true;
        }else{
            return $this->returnProperty('maxJumps');
        }
    }
    
    /**
     * returns or sets the stationID
     * @param integer $stationID
     * @return integer
     */
    public function stationID($stationID = null) {
        if ($stationID!== null) {
            $this->assoc['stationID'] = $stationID;
            return true;
        }else{
            return $this->returnProperty('stationID');
        }
    }
    
    /**
     * returns or sets the id
     * @param integer $id
     * @return integer
     */
    public function id($id = null) {
        if ($id!== null) {
            $this->assoc['id'] = $id;
            return true;
        }else{
            return $this->returnProperty('id');
        }
    }
    
    /**
     * returns or sets the position
     * @param array $position ([0]=>x,[1]=>y,[2]=>z)
     * @return array
     */
    public function evePosition($position = null) {
        if ($position!== null) {
            $this->assoc['x'] = $position[0];
            $this->assoc['y'] = $position[1];
            $this->assoc['z'] = $position[2];
            return true;
        }else{
            $x = $this->returnProperty('x');
            $y = $this->returnProperty('y');
            $z = $this->returnProperty('z');
            if ($x===null OR $y===null OR $z===null) {
                return null;
            }
            $position =[$x,$y,$z];
            return $position;
        }
    }
    
    /**
     * returns or sets the accountID
     * @param integer $accountID
     * @return integer
     */
    public function accountID($accountID = null) {
        if ($accountID!== null) {
            $this->assoc['accountID'] = $accountID;
            return true;
        }else{
            return $this->returnProperty('accountID');
        }
    }
    
    /**
     * returns or sets the groupID
     * @param integer $groupID
     * @return integer
     */
    public function groupID($groupID = null) {
        if ($groupID!== null) {
            $this->assoc['groupID'] = $groupID;
            return true;
        }else{
            return $this->returnProperty('groupID');
        }
    }
    
    /**
     * returns or sets the jumps
     * @param integer $jumps
     * @return integer
     */
    public function jumps($jumps = null) {
        if ($jumps!== null) {
            $this->assoc['jumps'] = $jumps;
            return true;
        }else{
            return $this->returnProperty('jumps');
        }
    }
    
    /**
     * returns or sets the jumpsSafe
     * @param integer $jumpsSafe
     * @return integer
     */
    public function jumpsSafe($jumpsSafe = null) {
        if ($jumpsSafe!== null) {
            $this->assoc['jumpsSafe'] = $jumpsSafe;
            return true;
        }else{
            return $this->returnProperty('jumpsSafe');
        }
    }
    
    /**
     * returns or sets the distance
     * @param integer $distance
     * @return integer
     */
    public function distance($distance = null) {
        if ($distance!== null) {
            $this->assoc['distance'] = $distance;
            return true;
        }else{
            return $this->returnProperty('distance');
        }
    }
    
    /**
     * returns or sets the sourceSolarSystemID
     * @param integer $sourceSolarSystemID
     * @return integer
     */
    public function sourceSolarSystemID($sourceSolarSystemID = null) {
        if ($sourceSolarSystemID!== null) {
            $this->assoc['sourceSolarSystemID'] = $sourceSolarSystemID;
            return true;
        }else{
            return $this->returnProperty('sourceSolarSystemID');
        }
    }
    
    /**
     * returns or sets the solarSystemID
     * @param integer $solarSystemID
     * @return integer
     */
    public function solarSystemID($solarSystemID = null) {
        if ($solarSystemID!== null) {
            $this->assoc['solarSystemID'] = $solarSystemID;
            return true;
        }else{
            return $this->returnProperty('solarSystemID');
        }
    }
    
    public function save() {
        $this->assoc['id']=$this->writeToDb(true);
        return $this->assoc['id'];
    }
    
    public function delete() {
        $id = $this->returnProperty('id');
        if ($id===null) {
            return false;
        }
        $accountID = \Controller\SessionController::session()->accountID();
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        //delete where id matches and the groupID belongs to a group the account is a member of
        $query ='DELETE p FROM '.$prefix.'poi p, '.$prefix.'groupmember m '
                . 'WHERE p.id = :id AND p.groupID=m.groupID AND m.accountID = :accountID';
        $rowCount = \Db\Sql::execute($query,[
            ':id'=>$id,
            ':accountID'=>$accountID
        ]);
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
}
