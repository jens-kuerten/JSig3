<?php

namespace Model;

class MapSystem extends BaseModel {

    /**
     * associative array of mapsystemproperties    
     * @var array
     */
    protected $assoc = [];
    protected $tableName = 'mapsystem';
    
    protected $pois;
    protected $intels;
    protected $signatures;


    protected $poiResult;

    public function __construct($param) {
        if (is_numeric($param)) {
            //load solarsystemID from SDE
            $this->loadFromSDE($param);
        } else if (is_string($param)) {
            //load solarSystemName from SDE
            $this->loadFromSDE($param);
        } else if (is_array($param)) {
            //load from associative array
            $this->loadFromAssoc($param);
        }
    }           
    
    /**
     * returns position in the Eve galaxy
     * [0]=>x,[1]=>y,[2]=>z
     * @return array
     */
    public function evePosition() {
        $positionArray = array();
        $positionArray[0] = $this->assoc['x'];
        $positionArray[1] = $this->assoc['y'];
        $positionArray[2] = $this->assoc['z'];
        return $positionArray;
    }                   

    /**
     * returns the distance between system and provided position array in meters
     * @param array $positionArray
     * @return float
     */
    public function getDistance(array $positionArray) {
        $position = $this->evePosition();
        return sqrt(pow($position[0] - $positionArray[0], 2) + pow($position[1] - $positionArray[1], 2) + pow($position[2] - $positionArray[2], 2));
    }

    /**
     * 
     * @param string $solarSystemID
     * @return string
     */
    public function solarSystemID() {
        return $this->returnProperty('solarSystemID');
    }
    
    /**
     * 
     * @param string $solarSystemName
     * @return string
     */
    public function solarSystemName($solarSystemName = null) {
        if ($solarSystemName !== null) {
            $this->assoc['solarSystemName'] = (string) $solarSystemName;
        } else {
            return (string)$this->returnProperty('solarSystemName');
        }
    }
    
    /**
     * 
     * @return integer
     */
    public function solarSystemClass() {
        return $this->returnProperty('solarSystemClass');
    }   
    
    /**
     * returns or sets the mapLabel
     * @param string $mapLabel
     * @return string
     */
    public function mapLabel($mapLabel=null) {
        if ($mapLabel!== null) {
            $this->assoc['label'] = (string)$mapLabel;
        }else{
            return $this->returnProperty('label');
        }
    }
    
    /**
     * position on the map [0]=>x,[1]=>y
     * @param array $mapPosition
     * @return array
     */
    public function mapPosition($mapPosition = null) {
        if ($mapPosition!== null) {
            $this->assoc['mapX'] = $mapPosition[0];
            $this->assoc['mapY'] = $mapPosition[1];
            return true;
        }else{
            if ($this->returnProperty('mapX')==null) {
                return null;
            }
            $position[0] = $this->returnProperty('mapX');
            $position[1] = $this->returnProperty('mapY');
            return $position;
        }
    }
    
    /**
     * 
     * @param string $tabID
     * @return string
     */
    public function tabID($tabID = null) {
        if ($tabID!== null) {
            $this->assoc['tabID'] = $tabID;
        }else{
            return $this->returnProperty('tabID');
        }
    }
    
    /**
     * array of solarSystemIDs that are connected via a gate
     * @return array
     */
    public function gates() {
        $gateStr = $this->returnProperty('gates');
        if ($gateStr!==null) {
            return explode(',', $gateStr);
        }
        return array();
    }
    
    /**
     * 
     * @param boolean $locked
     * @return boolean
     */
    public function locked($locked = null) {
        if ($locked !== null) {
            $this->assoc['locked']=$locked;
        }else{
            return $this->returnProperty(['locked']);
        }
    }
    
    /**
     * 
     * @param boolean $home
     * @return boolean
     */
    public function home($home = null) {
        if ($home !== null) {
            $this->assoc['home']=$home;
        }else{
            return $this->returnProperty('home');
        }
    }
    
    public function statics() {
        $statics = [];
        $static1 = $this->returnProperty('static1');
        $static2 = $this->returnProperty('static2');
        $static3 = $this->returnProperty('static3');
        if ($static1!==null) {
            array_push($statics, $static1);
        }
        if ($static2!==null) {
            array_push($statics, $static2);
        }
        if ($static3!==null) {
            array_push($statics, $static3);
        }
        return $statics;
    }
    
    /**
     * returns an array of pois that are in range of the system
     * @param array $poiResult database result of pois, if not supplied we will get them automatically
     * @return array<\Model\Poi>
     */
    public function returnPois($poiResult = null) {
        $tabID = $this->returnProperty('tabID');
        if ($tabID===null) {
            trigger_error('can\'t load POIs from a system that has no tabID');
        }
        if ($this->pois===null) {
            $this->loadPois($poiResult);
        }
        return $this->pois;
    }
    
    /**
     * returns an array of the system's intel
     * @return array<\Model\Intel>
     */
    public function returnIntels() {
        if ($this->intels===null) {
            $this->loadIntels();
        }
        return $this->intels;
    }
    
    public function returnSignatures() {
        if ($this->signatures===null) {
            $this->loadSignatures();
        }
        return $this->signatures;
    }
    
    //------------protected--------------            

    /**
     * loads the system from the SDE table
     * @global type $dbPrefix
     * @param string/integer $system
     */
    protected function loadFromSDE($system) {
        $prefix = \Db\Sql::dbPrefix();

        $query = 'SELECT * FROM ' . $prefix . 'systemlist ';
        if (is_numeric($system)) {
            $query .='WHERE solarSystemID = :system';
        } else if (is_string($system)) {
            $query .='WHERE solarSystemName = :system';
        } else {
            throw new Exception('not a systemID or systemName');
        }

        $param[':system'] = $system;

        $row = \Db\Sql::queryRow($query, $param);

        if (!empty($row)) {
            return $this->loadFromAssoc($row);
        } else {
            //system not found
            if (is_numeric($system)) {
                $this->solarSystemID = $system;
                $this->solarSystemName = 'unknown System';
                return true;
            } else if (is_string($system)) {
                $this->solarSystemName = $system;
                return true;
            } else {
                throw new Exception('not a systemID or systemName');
            }
        }
    }                
    
    private function recalculatePois($poiResult) {
        $this->pois = [];        
        if (!empty($poiResult)) {
            //precalculate the routes for k-space
            if ($this->isKSpace()) {
                $route = new \Lib\Route($this->solarSystemID(),false);
                $routeSafe = new \Lib\Route($this->solarSystemID(),true);
            }            
            
            //go through pois and decide which are in range
            foreach($poiResult as $poiAssoc) {
                $poi = new \Model\Poi($poiAssoc);
                //if it's kSpace, calculate jumps
                if ($this->isKSpace()) {
                    $jumps = $route->jumps($poi->solarSystemID());
                    $jumpsSafe = $routeSafe->jumps($poi->solarSystemID());                                    
                }else{
                    if ($poi->type()==='hub') {
                        continue; //w-space has no tradehubs
                    }
                     //w-space isn't safe :P
                    $jumpsSafe = -1;
                    //it's either this system or unreachable
                    $jumps = $poi->solarSystemID()==$this->solarSystemID()?0:-1; 
                }
                $distance = $this->calculateDistance($poi->evePosition());
                
                if (($jumps>$poi->maxJumps() OR $jumps==-1) AND $poi->maxJumps()>-1) {
                    continue; //to many jumps or not reachable
                }
                if (($distance>$poi->maxLy() OR $distance==-1)AND $poi->maxLy()>-1) {
                    continue; //to far away
                }
                
                //is in range
                $poi->jumps($jumps);
                $poi->jumpsSafe($jumpsSafe);
                $poi->distance($distance); 
                $poi->sourceSolarSystemID($this->solarSystemID());
                
                //add to pois
                $this->pois[] = $poi;
            }
        }        
        return true;
    }
    /**
     * loads all pois in range
     * @param array $poiResult
     */
    protected function loadPois($poiResult = NULL) {
        //if no poiResult supplied, try getting pois from cache
        if ($poiResult===null) {
            //try to get them from cache
            $cachedPois = \Controller\PoiController::getCache($this->tabID(), $this->solarSystemID());
            if ($cachedPois!==false) {
                //found them in cache->load them
                $this->pois = $cachedPois;
                return true;
            }
            
            //didn't find them in cache->load all pois for this tab
            $poiResult = \Controller\PoiController::returnPoiResult($this->tabID());
        }
        
        //not in cache->recalculate form poiResult
        $this->recalculatePois($poiResult);
        
        //cache result, but only for k-space  (only the k-space jump calculation is expensive - caching w-space pois would be pointless)
        if ($this->isKSpace()) {
            \Controller\PoiController::cachePois($this->tabID(), $this->solarSystemID(), $this->pois);
        }        
    }
    
    protected function loadIntels() {
        $this->intels = \Controller\IntelController::returnIntels($this->solarSystemID(), $this->tabID());
    }
    
    protected function loadSignatures() {
        $this->signatures = \Controller\SignatureController::returnSignatures($this->solarSystemID(), $this->tabID());
    }
    
    /**
     * returns the distance in Ly
     * @param array $evePosition [0]=>x,[1]=>x,[2]=>z
     * @return integer
     */
    public function calculateDistance($evePosition) {
        $systemPosition = $this->evePosition();
        if ($systemPosition===null) {
            return null;
        }
        return (sqrt(pow($systemPosition[0]-$evePosition[0],2) + pow($systemPosition[1]-$evePosition[1],2) + pow($systemPosition[2]-$evePosition[2],2))/9.4605284e+15);
    } 
    
    public function isKSpace() {
        $class = $this->solarSystemClass();
        if ($class==7 OR $class==8 OR $class==9) {
            return true;
        }
        return false;
    }

}
