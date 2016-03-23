<?php

namespace Controller;

class PoiController extends BaseController {
    
    /**
     * creates a poi and stores it to the database
     * @param \Model\MapSystem $mapSystem
     * @param string $type
     * @param string $description
     * @param integer $accountID
     * @param integer $groupID
     * @return \Model\Poi
     */
    public static function createPoi(\Model\MapSystem $mapSystem,$type,$description,$accountID,$groupID) {
        $poi = new \Model\Poi();
        
        $poi->description($description);
        $poi->type($type);
        $poi->groupID($groupID);
        $poi->accountID($accountID);
        
        $poi->evePosition($mapSystem->evePosition());
        $poi->solarSystemID($mapSystem->solarSystemID());
        $poi->solarSystemName($mapSystem->solarSystemName());
        if ($type==='cap') {
            $poi->maxLy(5);
            $poi->maxJumps(-1);
        }else{
            $poi->maxLy(-1);
            $poi->maxJumps(5);
        }
        
        $poi->save();
        self::invalidateCache($groupID);
        return $poi;        
    }
    
    /**
     * deletes a poi
     * @param integer $id
     * @return boolean
     */
    public static function deletePoi($id,$groupID) {
        $poi = new \Model\Poi();
        $poi->id($id);
        self::invalidateCache($groupID);
        return $poi->delete();
    }
    
    public static function cachePois($tabID,$solarSystemID,$pois) {
        $assocarr = [];
        foreach($pois as $poi) {
            $assocarr[] = $poi->returnAssoc();
        }        
        $cache = json_encode($assocarr);        
        $prefix = \Db\Sql::dbPrefix();
        $query = 'REPLACE INTO '.$prefix.'poicache (groupID,solarSystemID,cache) '
                . 'SELECT groupID,:solarSystemID,:cache FROM '.$prefix.'tab WHERE tabID=:tabID';
        $rowCount = \Db\Sql::execute($query,[
            ':tabID'=>$tabID,
            ':cache'=>$cache,
            ':solarSystemID'=>$solarSystemID
        ]);
        if ($rowCount>0) {
            return true;
        }
        return false;
    }
    
    public static function getCache($tabID,$solarSystemID=null) {
        $prefix = \Db\Sql::dbPrefix();
        $param = [];
        $querySolarSystem = '';
        
        if ($solarSystemID!==null) {
            $querySolarSystem = ' AND p.groupID = t.groupID AND p.solarSystemID = :solarSystemID';
            $param[':solarSystemID']=$solarSystemID;
        }
        $param[':tabID']=$tabID;
        $query = 'SELECT p.cache,p.solarSystemID FROM '.$prefix.'poicache as p, '.$prefix.'tab as t '
                . 'WHERE t.tabID = :tabID '.$querySolarSystem.' GROUP BY p.solarSystemID';
        $result = \Db\Sql::query($query,$param);
        if (!empty($result)) {
            $pois = [];
            foreach($result as $row) {
                $poiAssocs = json_decode($row['cache'],true);
                foreach($poiAssocs as $poiAssoc) {
                    $pois[] = new \Model\Poi($poiAssoc);                
                }
            }
            
            return $pois;
        }
        return false;
    }
    
    public static function returnPoiResult($tabID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT p.* FROM '.$prefix.'poi as p, '.$prefix.'tab as t '
                . 'WHERE (t.tabID = :tabID AND t.groupID = p.groupID) OR p.groupID = 0 GROUP BY p.id';
        return \Db\Sql::query($query,[
            ':tabID'=>$tabID
        ]);
    }
    
    public static function returnAccPOIs($accountID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT p.* FROM '.$prefix.'poi as p, '.$prefix.'groupmember as m '
                . 'WHERE p.groupID = 0 OR (p.groupID = m.groupID AND m.accountID = :accountID )GROUP BY p.id';
        $result = \Db\Sql::query($query,[
            ':accountID'=>$accountID
        ]);
        $accPOIs = [];
        foreach ($result as $row) {
            $accPOIs[] = new \Model\Poi($row);
        }
        return $accPOIs;
    }
    
    public static function invalidateCache($groupID) {        
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'DELETE FROM '.$prefix.'poicache WHERE groupID = :groupID';
        \Db\Sql::execute($query,[
            ':groupID'=>$groupID
        ]);
        return true;
    }
}
