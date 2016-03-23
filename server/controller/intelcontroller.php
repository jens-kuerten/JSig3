<?php


namespace Controller;
class IntelController extends BaseController {
    
    
    public static function createIntel($value,$type,$groupID,$solarSystemID) {
        if ($type=='intel') {
            $eveID = \Lib\EveXmlApi::getEveID($value);            
            if (\Lib\EveXmlApi::isCorp($eveID)) {
                $type='corp';
            }else if ($eveID!=0) {
                $type='ally';
            }
        }else{
            $eveID = 0;
        }
        $intel = new \Model\Intel();
        $intel->value($value);
        $intel->eveID($eveID);
        $intel->type($type);
        $intel->groupID($groupID);
        $intel->solarSystemID($solarSystemID);
        $intel->creationTime(time());
        $intel->save();
        
        \Controller\PipeController::sendGroupMessage($groupID, 'addIntel', $intel->returnAssoc());
    }
    
    /**
     * returns an array of intels of the solarsystemID from the tabID
     * @param integer $solarSystemID
     * @param integer $tabID
     * @return array<\Model\Intel>
     */
    public static function returnIntels($solarSystemID,$tabID) {
        $intels = [];
        
        $prefix = \Db\Sql::dbPrefix();        
        $query = 'SELECT i.* FROM '.$prefix.'intel as i,'.$prefix.'tab as t '
                . 'WHERE i.groupID = t.groupID AND t.tabID = :tabID AND i.solarSystemID = :solarSystemID';
        $result = \Db\Sql::query($query,[
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID
        ]);
        if (!empty($result)) {
            foreach($result as $intelAssoc) {
                $intels[] = new \Model\Intel($intelAssoc);
            }
        }
        return $intels;
    }
    
    public static function deleteIntel($id,$groupID) {
        $intel = new \Model\Intel();
        $intel->id($id);
        if ($intel->delete()) {
            \Controller\PipeController::sendGroupMessage($groupID, 'deleteIntel', ['id'=>$id]);
        }
    }
}
