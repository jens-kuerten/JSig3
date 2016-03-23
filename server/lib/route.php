<?php

namespace Lib;

class jNode {       
    public $visited = false;
    public $distance = -1;//-1 unreachable
    public $solarSystemID = 0;
    public $security = 0;    
    public $parent = 0;
    public $connections = array();    
    
    public function __construct($solarSystemID,$security,$gates) {        
        $this->solarSystemID = $solarSystemID;
        $this->security = $security;
        $this->connections = explode(',', $gates);        
    }
    public function visit($node) {
        if ($this->visited===true) {
            return false; //we already visited that node
        }
        $this->visited = true;
        $this->distance = $node->distance + 1;   
        $this->parent = $node->solarSystemID;            
        return true;
    }

}

class Route {
    protected $systemArray = array();
    protected $fromSolarSystemID=0;
    protected $queue = array();
    protected $systemsLoaded = false;
    protected $safe = false;
    
    /**
     * creates a route from the given solarsystem
     * @param string $fromSolarSystemID ID or Name of the starting solar System
     * @param boolean $safe only highsec (true)
     */
    public function __construct($fromSolarSystemID,$safe = false) {
        $this->safe = $safe;
        if (!is_numeric($fromSolarSystemID)) {
            $this->fromSolarSystemID = $this->getSolarSystemID($fromSolarSystemID);
            if ($this->fromSolarSystemID===false) {
                $this->fromSolarSystemID = 0;
            }
        }else{
            $this->fromSolarSystemID = $fromSolarSystemID;
        }
        
        //check if routes are already in cache, if not calculate then and safe them to the cache
        if(!$this->loadCache()) {            
            $this->loadSystems();
            $this->calculateRoutes();
            $this->writeCache();
        }
  
    }
    
    /**
     * returns the number of jumps to the target system
     * @param string $targetSolarSystemID ID target solar System
     * @return integer number of jumps
     */
    public function jumps($targetSolarSystemID) {        
        if (!isset($this->systemArray[$targetSolarSystemID])) { 
            return -1;
        }
        return $this->systemArray[$targetSolarSystemID]->distance;
    }
    
    protected function loadSystems() {
        $dbPrefix = \Db\Sql::dbPrefix();
        if ($this->systemsLoaded===true) {
            return true;
        }
        $this->systemsLoaded=true;
        //load systems that have gate connections
        $query ='SELECT solarSystemID,security,gates FROM '.$dbPrefix.'systemlist WHERE gates!=""';
        $result = \Db\Sql::query($query);
        //and create their system object
        foreach($result as $row) {
            $this->systemArray[$row['solarSystemID']] = new jNode($row['solarSystemID'], $row['security'],$row['gates']);                        
        }
        return true;
    }
    
    protected function calculateRoutes() {
        //add starting system to queue and mark it as visited
        if (!isset($this->systemArray[$this->fromSolarSystemID])) {            
            return false;
        }
        $this->queue[0] = $this->systemArray[$this->fromSolarSystemID];
        $this->queue[0]->visit($this->queue[0]);          
        
        while(count($this->queue)>0)
        {   
            //take node from front of the queue
            $node = array_shift($this->queue);            
            //get adjacent nodes
            $connections = $node->connections;              
            foreach($connections as $key=>$connectedNodeID)
            {                                   
                if (!isset($this->systemArray[$connectedNodeID])) {                    
                    continue;
                }
                $connectedNode = $this->systemArray[$connectedNodeID];
                //mark adjacent nodes visited, add to queue and set distance to parent distance +1
                if ($connectedNode->visit($node)===false) {
                    continue;
                }
                if (($this->safe===false) OR ($connectedNode->security>=0.45))
                {   
                    //add node to end of queue, but only if node is safe, shortest route                    
                    array_push($this->queue, $connectedNode);                    
                }    
           
            }              
        }
        return true;
    }      
   
    protected function loadCache() {
        $dbPrefix = \Db\Sql::dbPrefix();
        
        $query = 'SELECT * FROM '.$dbPrefix.'route WHERE fromSolarSystemID = :systemid AND safe = :safe';
        $row = \Db\Sql::queryRow($query, array(":systemid"=>  $this->fromSolarSystemID,":safe"=>$this->safe));
        
        if (empty($row)) {
            return false;
        }
        $targetList = explode(',', $row['targetList']);                        
        $parentList = explode(',', $row['parentList']);        
        $targetJumps = explode(',', $row['targetJumps']);
        
        foreach ($targetList as $key=>$target) {
            if (!isset($this->systemArray[$target])) {
                $this->systemArray[$target] = new jNode($target, 0, '');
            }
            $this->systemArray[$target]->visited = true;
            $this->systemArray[$target]->distance = $targetJumps[$key];
            $this->systemArray[$target]->parent = $parentList[$key];
        }
        return true;
    }
    
    protected function writeCache() {
        $dbPrefix = \Db\Sql::dbPrefix();
        
        $targetArr = array();
        $parentArr = array();
        $jumpArray = array();
        foreach($this->systemArray as $system) {           
            $targetArr[] =$system->solarSystemID;
            $jumpArray[] = $system->distance;
            $parentArr[] = $system->parent;            
        }
        
        $query = 'INSERT INTO '.$dbPrefix.'route (fromSolarSystemID,targetList,parentList,targetJumps,safe) VALUES '
                . '(:systemid,:targetlist,:parentlist,:targetjumps,:safe) ';
        $rowCount = \Db\Sql::execute($query,array(
            ":systemid"     =>  $this->fromSolarSystemID,
            ":targetlist"   =>  implode(',',$targetArr),
            ":parentlist"   =>  implode(',', $parentArr),
            ":targetjumps"  =>  implode(',', $jumpArray),
            ":safe"         =>  $this->safe
        ));
        
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
}

