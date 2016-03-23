<?php


namespace Model;

class Connection extends BaseModel {

    protected $tableName = 'connection';
    protected $assoc = [
        'fromSolarSystemID'=>0,
        'toSolarSystemID'=>0,
        'tabID'=>0
    ];

    /**
     * 
     * @param array/integer $param1 assoc or fromSolarSystemID
     * @param integer $param2   toSolarSystemID
     * @param integer $param3   tabID
     */
    public function __construct($param1, $param2 = null, $param3 = null) {
        if (is_array($param1)) {
            $this->loadFromAssoc($param1);
        } else if ($param1 !== null AND $param2 !== null AND $param3 !== null) {
            $this->assoc['fromSolarSystemID'] = $param1;
            $this->assoc['toSolarSystemID'] = $param2;
            $this->assoc['tabID'] = $param3;
        }        
    }

    /**
     * returns or sets the massStage
     * @param integer $massStage
     * @return integer
     */
    public function massStage($massStage = null) {
        if ($massStage !== null) {
            $this->assoc['massStage'] = (integer) $massStage;
            return true;
        } else {
            return (integer) $this->returnProperty('massStage');
        }
    }

    /**
     * returns or sets the timeStage
     * @param integer $timeStage
     * @return integer
     */
    public function timeStage($timeStage = null) {
        if ($timeStage !== null) {
            $this->assoc['timeStage'] = (integer) $timeStage;
            return true;
        } else {
            return (integer) $this->returnProperty('timeStage');
        }
    }

    /**
     * returns or sets the massed that has passed through the connection
     * @param integer $massPassed
     * @return integer
     */
    public function massPassed($massPassed = null) {
        if ($massPassed !== null) {
            $this->assoc['massPassed'] = $massPassed;
            return true;
        } else {
            return $this->returnProperty('massPassed');
        }
    }

    /**
     * returns or sets the creationTime
     * @param integer $creationTime
     * @return integer
     */
    public function creationTime($creationTime = null) {
        if ($creationTime !== null) {
            $this->assoc['creationTime'] = $creationTime;
            return true;
        } else {
            return $this->returnProperty('creationTime');
        }
    }

    /**
     * returns or sets the eolTime
     * @param integer $eolTime
     * @return integer
     */
    public function eolTime($eolTime = null) {
        if ($eolTime !== null) {
            $this->assoc['eolTime'] = $eolTime;
            return true;
        } else {
            return $this->returnProperty('eolTime');
        }
    }

    /**
     * returns or sets the tabID
     * @param integer $tabID
     * @return integer
     */
    public function tabID($tabID = null) {
        if ($tabID !== null) {
            $this->assoc['tabID'] = $tabID;
            return true;
        } else {
            return $this->returnProperty('tabID');
        }
    }

    /**
     * returns or sets the fromSolarSystemID
     * @param integer $fromSolarSystemID
     * @return integer
     */
    public function fromSolarSystemID($fromSolarSystemID = null) {
        if ($fromSolarSystemID !== null) {
            $this->assoc['fromSolarSystemID'] = $fromSolarSystemID;
            return true;
        } else {
            return $this->returnProperty('fromSolarSystemID');
        }
    }

    /**
     * returns or sets the toSolarSystemID
     * @param integer $toSolarSystemID
     * @return integer
     */
    public function toSolarSystemID($toSolarSystemID = null) {
        if ($toSolarSystemID !== null) {
            $this->assoc['toSolarSystemID'] = $toSolarSystemID;
            return true;
        } else {
            return $this->returnProperty('toSolarSystemID');
        }
    }

    public function save() {
        //we need to check if there is already one with an opposite direction
        $fromSolarSystemID = $this->assoc['fromSolarSystemID'];
        $toSolarSystemID = $this->assoc['toSolarSystemID'];
        $tabID = $this->assoc['tabID'];

        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;

        $query = 'SELECT tabID from ' . $prefix . $tableName . ' WHERE '
                . 'fromSolarSystemID = :fromSolarSystemID AND toSolarSystemID = :toSolarSystemID '
                . 'AND tabID = :tabID';
        $row = \Db\Sql::queryRow($query, [
                    ':fromSolarSystemID' => $toSolarSystemID,
                    ':toSolarSystemID' => $fromSolarSystemID,
                    ':tabID' => $tabID
        ]);
        if (!empty($row)) {
            //exists -> switch to and from solarSystemID
            $this->assoc['fromSolarSystemID'] = $toSolarSystemID;
            $this->assoc['toSolarSystemID'] = $fromSolarSystemID;
        }

        //now we can save it
        $this->writeToDb();
    }
    
    public function create() {
        //we need to check if there is already one with an opposite direction
        $fromSolarSystemID = $this->assoc['fromSolarSystemID'];
        $toSolarSystemID = $this->assoc['toSolarSystemID'];
        $tabID = $this->assoc['tabID'];

        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;

        $query = 'SELECT tabID from ' . $prefix . $tableName . ' WHERE '
                . 'fromSolarSystemID = :fromSolarSystemID AND toSolarSystemID = :toSolarSystemID '
                . 'AND tabID = :tabID';
        $row = \Db\Sql::queryRow($query, [
                    ':fromSolarSystemID' => $toSolarSystemID,
                    ':toSolarSystemID' => $fromSolarSystemID,
                    ':tabID' => $tabID
        ]);
        if (!empty($row)) {
            //connection already exists
            return false;
        }
        
        return $this->createToDb();
    }
    
    public function update() {
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        $tableColumns = \Db\tables::$$tableName;
        
        $query = 'UPDATE '.$prefix.'connection SET ';
        $update = '';
        $param = [];
        $first = true;
        foreach ($tableColumns as $column) {
            //search for column in assoc
            if (isset($this->assoc[$column])) {
                if ($first === true) {
                    $first = false;
                } else {
                    $update .=',';
                }                
                $update .=$column . '=:' . $column . ' ';
                $param[':' . $column] = $this->assoc[$column];
            } else {
                
            }
        }
        $query .=$update.
                ' WHERE ((fromSolarSystemID=:fromSolarSystemID AND toSolarSystemID=:toSolarSystemID) OR '
                . '(fromSolarSystemID=:toSolarSystemID AND toSolarSystemID=:fromSolarSystemID))'
                . ' AND tabID IN '
                . '(select tabID FROM '.$prefix.'tab  WHERE groupID IN '
                . '(SELECT groupID FROM '.$prefix.'tab  WHERE tabID = :tabID))';
        
        $rowCount = \Db\Sql::execute($query,$param);
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
    
    public function delete() {
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        
        $query = 'DELETE FROM '.$prefix.$tableName.
                ' WHERE tabID = :tabID AND ((fromSolarSystemID=:fromSolarSystemID AND toSolarSystemID=:toSolarSystemID) OR '
                . '(fromSolarSystemID=:toSolarSystemID AND toSolarSystemID=:fromSolarSystemID))';
        $rowCount = \Db\Sql::execute($query,[
            ':tabID'=>$this->returnProperty('tabID'),
            ':fromSolarSystemID'=>$this->returnProperty('fromSolarSystemID'),
            ':toSolarSystemID'=>$this->returnProperty('toSolarSystemID')
        ]);
        
        if ($rowCount>0) {
            return true;
        }
        return false;
    }

}
