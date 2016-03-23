<?php


namespace Model;
class Tab extends BaseModel {
    protected $tableName='tab';
    protected $assoc= [        
        'groupID'=>0
    ];
    
    protected $mapSystems;
    protected $connections;
    protected $intels;
    protected $signatures;
    protected $pilots;


    public function __construct($param = null) {
        if (is_array($param)) {
            $this->loadFromAssoc($param);
        }else if(is_numeric($param)){
            $this->loadFromDb($param);
        }
    }
    
    /**
     * returns or sets the groupID
     * @param integer $groupID
     * @return integer
     */
    public function groupID($groupID = null) {
        if ($groupID!==null) {
            $this->assoc['groupID']=(integer)$groupID;
            return true;
        }else{
            return (integer)$this->returnProperty('groupID');
        }
    }
    
    /**
     * returns or sets the tabName
     * @param string $tabName
     * @return string
     */
    public function tabName($tabName = null) {
        if ($tabName!== null) {
            $this->assoc['tabName'] = $tabName;
            return true;
        }else{
            return $this->returnProperty('tabName');
        }
    }
    
    public function imgURL($imgURL = null) {
        if ($imgURL!== null) {
            $this->assoc['imgURL'] = $imgURL;
            return true;
        }else{
            return $this->returnProperty('imgURL');
        }
    }
    
    /**
     * returns the tabID
     * @return integer
     */
    public function tabID() {
        return (integer)$this->returnProperty('tabID');
    }
    
    
    
    /**
     * saves the tab, returns tabID if new tab
     * @return mixed
     */
    public function save() {
        if ($this->returnProperty('tabID')===null) {
            $this->assoc['tabID'] = $this->writeToDb(true);
            return $this->assoc['tabID'];
        }else{
            $this->writeToDb();
            return true;
        }
    }
    
    /**
     * returns an array of connections
     * @return array<\Model\Connection>
     */
    public function returnConnections() {
        if ($this->connections===null) {
            $this->loadConnections();
        }
        return $this->connections;
    }
    
    /**
     * returns an array of mapsystems
     * @return array<\Model\MapSystem>
     */
    public function returnMapSystems() {
        if ($this->mapSystems===null) {
            $this->loadMapSystems();
        }
        return $this->mapSystems;
    }
    
    /**
     * returns an array of intels
     * @return array<\Model\Intel>
     */
    public function returnIntels() {
        if ($this->intels===null) {
            $this->loadIntels();
        }
        return $this->intels;
    }
    
    /**
     * returns an array of pilots
     * @return array<\Mocel\Pilots>
     */
    public function returnPilots() {
        if ($this->pilots===null) {
            $this->loadPilots();
        }
        return $this->pilots;
    }
    
    /**
     * returns an array of signatures
     * @return array<\Model\Signature>
     */
    public function returnSignatures() {
        if ($this->signatures===null) {
            $this->loadSignatures();
        }
        return $this->signatures;
    }
    
    
    protected function loadFromDb($tabID) {
        $prefix = \Db\Sql::dbPrefix();
        $tableName = $this->tableName;
        $query = 'SELECT * FROM '.$prefix.$tableName.' WHERE tabID = :tabID';
        $row = \Db\Sql::queryRow($query,[
            ':tabID'=>$tabID
        ]);
        if (!empty($row)) {
            $this->loadFromAssoc($row);
            return true;
        }
        return false;
    }
    
    protected function loadConnections() {
        $tabID = $this->returnProperty('tabID');
        if ($tabID===null) {
            return false;
        }
        
        $this->connections = [];
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT * FROM '.$prefix.'connection WHERE tabID = :tabID';
        $result = \Db\Sql::query($query,[
            ':tabID'=>$tabID
        ]);
        if (!empty($result)) {
            foreach($result as $connectionAssoc) {
                $this->connections[] = new \Model\Connection($connectionAssoc);
            }
        }
    }
    
    protected function loadMapSystems() {
        $tabID = $this->returnProperty('tabID');
        if ($tabID===null) {
            return false;
        }
        
        $this->mapSystems = [];
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT * FROM '.$prefix.'mapsystem WHERE tabID = :tabID';
        $result = \Db\Sql::query($query,[
            ':tabID'=>$tabID
        ]);
        if (!empty($result)) {
            foreach($result as $systemAssoc) {
                $this->mapSystems[] = new \Model\MapSystem($systemAssoc);
            }
        }
        
    }
    
    protected function loadIntels() {
        $tabID = $this->returnProperty('tabID');
        if ($tabID===null) {
            return false;
        }
        
        $this->intels = [];
        $prefix = \Db\Sql::dbPrefix();
        //load intel from group that this tab is assigned to
        $query = 'SELECT i.* FROM '.$prefix.'intel as i, '.$prefix.'tab as t, '.$prefix.'mapsystem as m '
                . 'WHERE i.groupID = t.groupID AND t.tabID = :tabID AND m.tabID = t.tabID AND i.solarSystemID = m.solarSystemID';
        $result = \Db\Sql::query($query,[
            ':tabID'=>$tabID
        ]);
        if (!empty($result)) {
            foreach($result as $intelAssoc) {
                $this->intels[] = new \Model\Intel($intelAssoc);
            }
        }
    }
    
    protected function loadSignatures() {
        $tabID = $this->returnProperty('tabID');
        if ($tabID===null) {
            return false;
        }
        
        $this->signatures = [];
        $prefix = \Db\Sql::dbPrefix();
        //load signatures from group that this tab is assigned to
        $query = 'SELECT s.* FROM '.$prefix.'signature as s, '.$prefix.'tab as t, '.$prefix.'mapsystem as m '
                . 'WHERE s.groupID = t.groupID AND t.tabID = :tabID AND m.tabID = t.tabID AND s.solarSystemID = m.solarSystemID '
                . 'ORDER BY s.isAnom';
        $result = \Db\Sql::query($query,[
            ':tabID'=>$tabID
        ]);
        if (!empty($result)) {
            foreach($result as $signatureAssoc) {
                $this->signatures[] = new \Model\Signature($signatureAssoc);
            }
        }
    }
    
    protected function loadPilots() {
        $tabID = $this->returnProperty('tabID');
        $groupID = $this->returnProperty('groupID');
        if ($tabID==null OR $groupID==null) {
            return false;
        }
        
        $this->pilots = [];
        $prefix = \Db\Sql::dbPrefix();
        //load pilots from group that this tab is assigned to
        $query = 'SELECT s.characterName,s.solarSystemID,s.shipTypeName FROM '
                . ''.$prefix.'session s, '.$prefix.'mapsystem m, '.$prefix.'groupmember g '
                . 'WHERE s.accountID = g.accountID AND g.groupID = :groupID AND s.solarSystemID = m.solarSystemID AND m.tabID = :tabID AND s.sessionType = 2';
        $result = \Db\Sql::query($query,[
            ':tabID'=>$tabID,
            ':groupID'=>$groupID            
        ]);
        if (!empty($result)) {
            foreach($result as $row) {
                $this->pilots[] = new \Model\Pilot($row['characterName'], $row['shipTypeName'], $row['solarSystemID']);
            }
        }
    }
}
