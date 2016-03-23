<?php

namespace Model;

class Session extends BaseModel {

    protected $tableName = 'session';
    protected $assoc = [
        'sessionKey' => '',
        'access' => 0,
        'serialData' => ''
    ];
    
    public function __construct($sessionKey = null) {
        if ($sessionKey !== null) {
            //load from Db
            if (!$this->loadFromDb($sessionKey)) {
                $this->assoc['sessionKey'] = $sessionKey;
            }
        }
    }

    /**
     * returns  or sets the session key, null if not set
     * @param string $sessionKey
     * @return string
     */
    public function sessionKey($sessionKey = null) {
        if ($sessionKey !== null) {
            $this->assoc['sessionKey'] = $sessionKey;
            return true;
        } else {
            return $this->returnProperty($sessionKey);
        }
    }

    /**
     * returns or sets the serialData
     * @param string $serialData
     * @return string
     */
    public function serialData($serialData = null) {
        if ($serialData !== null) {
            $this->assoc['serialData'] = $serialData;
            return true;
        } else {
            return $this->returnProperty('serialData', '');
        }
    }

    /**
     * returns or sets the accountID
     * @param string $accountID
     * @return string
     */
    public function accountID($accountID = null) {
        if ($accountID !== null) {
            $this->assoc['accountID'] = $accountID;
            return true;
        } else {
            return $this->returnProperty('accountID', '');
        }
    }

    /**
     * returns or sets the characterName
     * @param string $characterName
     * @return string
     */
    public function characterName($characterName = null) {
        if ($characterName !== null) {
            $this->assoc['characterName'] = $characterName;
            return true;
        } else {
            return $this->returnProperty('characterName');
        }
    }

    /**
     * returns or sets the solarSystemID
     * @param steing $solarSystemID
     * @return string
     */
    public function solarSystemID($solarSystemID = null) {
        if ($solarSystemID !== null) {
            $this->assoc['solarSystemID'] = $solarSystemID;
            return true;
        } else {
            return $this->returnProperty('solarSystemID');
        }
    }

    /**
     * returns or sets the sessionType
     * @param string $sessionType
     * @return String
     */
    public function sessionType($sessionType = null) {        
        if ($sessionType !== null) {
            $this->assoc['sessionType'] = $sessionType;
            return true;
        } else {
            if ($this->returnProperty('sessionType')==1 OR $this->returnProperty('sessionType')==2) {
                if (\Lib\IgbApi::isTrusted()) {
                    return 2;
                }else {
                    return 1;
                }
            }
            return $this->returnProperty('sessionType');
        }
    }

    /**
     * returns or sets the shipTypeName
     * @param string $shipTypeName
     * @return string
     */
    public function shipTypeName($shipTypeName = null) {
        if ($shipTypeName !== null) {
            $this->assoc['shipTypeName'] = $shipTypeName;
            return true;
        } else {
            return $this->returnProperty('shipTypeName');
        }
    }
    
    /**
     * returns and sets the access time
     * @param integer $accessTime
     * @return integer
     */
    public function accessTime($accessTime = null) {
        if ($accessTime!==null) {
            $this->assoc['access']=(integer)$accessTime;
            return true;
        }else{
            return (integer)$this->returnProperty('access');
        }
    }

    /**
     * loads session from Database 
     * @param string $sessionKey sessionkey
     * @return boolean
     */
    protected function loadFromDb($sessionKey) {
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;

        $query = 'SELECT * FROM ' . $prefix . $tableName . ' WHERE sessionKey=:sessionKey';
        $row = \Db\Sql::queryRow($query, [
                    ':sessionKey' => $sessionKey
        ]);
        if (!empty($row)) {
            $this->assoc = array_merge($this->assoc, $row);
            return true;
        }
        return false;
    }

}
