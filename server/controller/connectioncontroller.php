<?php

namespace Controller;
class ConnectionController extends BaseController {
    public static function setMassStage($fromSolarSystemID,$toSolarSystemID,\Model\Tab $tab,$massStage) {
        $tabID = $tab->tabID();
        $connection = new \Model\Connection($fromSolarSystemID,$toSolarSystemID,$tabID);        
        $connection->massStage($massStage);
        if ($connection->update()) {
            $assoc = $connection->returnAssoc();
            $assoc['groupID']= $tab->groupID();
            \Controller\PipeController::sendGroupMessage($tab->groupID(), 'conMassStage', $assoc);
            return true;
        }else{
            return false;
        }
    }
    public static function setTimeStage($fromSolarSystemID,$toSolarSystemID,\Model\Tab $tab,$timeStage) {
        $tabID = $tab->tabID();
        $connection = new \Model\Connection($fromSolarSystemID,$toSolarSystemID,$tabID);        
        $connection->timeStage($timeStage);
        if ($timeStage=2) {
            $connection->eolTime(time());
        }
        if ($connection->update()) {
            $assoc = $connection->returnAssoc();
            $assoc['groupID']= $tab->groupID();
            \Controller\PipeController::sendGroupMessage($tab->groupID(), 'conTimeStage', $assoc);
            return true;
        }else{
            return false;
        }
    }
    public static function deleteConnection($fromSolarSystemID,$toSolarSystemID,$tabID) {
        $connection = new \Model\Connection($fromSolarSystemID,$toSolarSystemID,$tabID);
        $success = $connection->delete();
        if ($success==true) {
            self::sendPipeTabDeleteConnection($fromSolarSystemID, $toSolarSystemID, $tabID);
            return true;
        }else{
            return false;
        }
        
    }
    public static function addMass($fromSolarSystemID,$toSolarSystemID,\Model\Tab $tab,$mass) {
        $prefix = \Db\Sql::dbPrefix();
        $tabID = $tab->tabID();
        $query = 'UPDATE '.$prefix.'connection SET massPassed = massPassed + :mass '.
                ' WHERE ((fromSolarSystemID=:fromSolarSystemID AND toSolarSystemID=:toSolarSystemID) OR '
                . '(fromSolarSystemID=:toSolarSystemID AND toSolarSystemID=:fromSolarSystemID))'
                . ' AND tabID IN '
                . '(select tabID FROM '.$prefix.'tab  WHERE groupID IN '
                . '(SELECT groupID FROM '.$prefix.'tab  WHERE tabID = :tabID))';
        $rowCount = \Db\Sql::execute($query,[
            ':fromSolarSystemID'=>$fromSolarSystemID,
            ':toSolarSystemID'=>$toSolarSystemID,
            ':tabID'=>$tabID,
            ':mass'=>$mass
        ]);
        
        if ($rowCount>0) {
            $connection = new \Model\Connection($fromSolarSystemID,$toSolarSystemID,$tabID);
            $assoc = $connection->returnAssoc();
            $assoc['addMass']= $mass;
            $assoc['groupID']= $tab->groupID();
            \Controller\PipeController::sendGroupMessage($tab->groupID(), 'conAddMass',$assoc);
            return true;
        }
        return false;
    }
    
    public static function returnSisterConnection($fromSolarSytemID,$toSolarSystemID,$tabID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT * FROM '.$prefix.'connection WHERE ((fromSolarSystemID=:fromSolarSystemID AND toSolarSystemID=:toSolarSystemID) OR '
                . '(fromSolarSystemID=:toSolarSystemID AND toSolarSystemID=:fromSolarSystemID)) '
                . ' AND tabID IN '
                . '(select tabID FROM '.$prefix.'tab  WHERE groupID IN '
                . '(SELECT groupID FROM '.$prefix.'tab  WHERE tabID = :tabID)) LIMIT 1';
        $row = \Db\Sql::queryRow($query,[
            ':fromSolarSystemID'=>$fromSolarSytemID,
            ':toSolarSystemID'=>$toSolarSystemID,
            ':tabID'=>$tabID
        ]);
        if (!empty($row)) {
            return new \Model\Connection($row);
        }else{
            return null;
        }
    }
    
    
    protected static function sendPipeTabDeleteConnection($fromSolarSystemID,$toSolarSystemID,$tabID) {
        \Controller\PipeController::sendTabMessage($tabID, 'deleteConnection',
                [
                    'fromSolarSystemID'=>$fromSolarSystemID,
                    'toSolarSystemID'=>$toSolarSystemID,
                    'tabID'=>$tabID
                ]
                );
    }
}
