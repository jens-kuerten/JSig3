<?php


namespace Lib;
class Logger {
    public static function logConnection($fromSolarSystemID,$toSolarSystemID,$accountID,$groupID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'INSERT INTO '.$prefix.'connectionlog (fromSolarSystemID,toSolarSystemID,accountID,groupID) VALUES '
                . '(:fromSolarSystemID,:toSolarSystemID,:accountID,:groupID)';
        $rowCount = \Db\Sql::execute($query,[
            ':fromSolarSystemID'=>$fromSolarSystemID,
            ':toSolarSystemID'=>$toSolarSystemID,
            ':accountID'=>$accountID,
            ':groupID'=>$groupID
        ]);
    }
    public static function logError() {
        
    }
}
