<?php

namespace Model;

abstract class BaseModel {

    /**
     * associative array of all public model properties
     * @var array 
     */
    protected $assoc = [];

    /**
     * name of the table associated with the model
     * @var string 
     */
    protected $tableName = '';

    /**
     * save to database, on duplicate key update
     * @return boolean
     */
    public function save() {
        return $this->writeToDb();
    }
    
    /**
     * save to database, does NOT update on duplicate key
     * @return boolean
     */
    public function create() {
        return $this->createToDb();
    }
    
    /**
     *  returns a property of the model, returns $null if property not set
     * @param string $key
     * @param type $null
     * @return string
     */
    public function returnProperty($key,$null = null) {
        if (isset($this->assoc[$key])) {
            return $this->assoc[$key];
        }
        return $null;
    }
    
    public function returnAssoc() {
        return $this->assoc;
    }
    
    protected function loadFromAssoc($assoc) {
        $this->assoc = array_merge($this->assoc, $assoc);
    }
    
    /**
     * write model to table
     * @param boolean $returnID if true no update but returns inserted id
     * @return integer/boolean
     */
    protected function writeToDb($returnID = false) {
        $dbPrefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        $tableColumns = \Db\tables::$$tableName;

        $query = 'INSERT INTO ' . $dbPrefix . $tableName;
        $col = '(';
        $val = '(';
        $update = '';
        $param = [];
        $first = true;
        foreach ($tableColumns as $column) {
            //search for column in assoc
            if (isset($this->assoc[$column])) {
                if ($first === true) {
                    $first = false;
                } else {
                    $col .= ',';
                    $val .= ',';
                    $update .=$column!='timeCreated'?',':'';
                }
                $col .=$column;
                $val .=':' . $column . ' ';
                $update .=$column!='timeCreated'?($column . '=:' . $column . ' '):'';
                $param[':' . $column] = $this->assoc[$column];
            } else {
                
            }
        }
        $col .=')';
        $val .=')';

        $query .= $col . ' VALUES ' . $val;        
        if (!$returnID) { //update only if we don't expect to return an ID
            $query .=' ON DUPLICATE KEY UPDATE ' . $update;
        }        

        $rowCount = \Db\Sql::execute($query, $param,$returnID);
        
        if($returnID) {
            return $rowCount;
        }
        
        if ($rowCount > 0) {
            return true;
        }
        return false;
    }
    
    protected function createToDb() {
        $dbPrefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        $tableColumns = \Db\tables::$$tableName;

        $query = 'INSERT IGNORE INTO ' . $dbPrefix . $tableName;
        $col = '(';
        $val = '(';     
        $param = [];
        $first = true;
        foreach ($tableColumns as $column) {
            //search for column in assoc
            if (isset($this->assoc[$column])) {
                if ($first === true) {
                    $first = false;
                } else {
                    $col .= ',';
                    $val .= ',';
                }
                $col .=$column;
                $val .=':' . $column . ' ';               
                $param[':' . $column] = $this->assoc[$column];
            } else {
                
            }
        }
        $col .=')';
        $val .=')';
        $query .= $col . ' VALUES ' . $val;
                
        $rowCount = \Db\Sql::execute($query, $param);
        
        if ($rowCount > 0) {            
            return true;
        }
        return false;
    }

}
