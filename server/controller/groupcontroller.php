<?php


namespace Controller;

class GroupController extends BaseController{
    public static function getCorpGroupID($corporationName,$corporationID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT groupID FROM '.$prefix.'group WHERE type=\'corporation\' AND eveID = :corporationID';
        $row = \Db\Sql::queryRow($query,[':corporationID'=>$corporationID]);
        if (!empty($row)) {
            return $row['groupID'];
        }else{
            
            if (\Server::get('conf_usewhitelist')==true) {
                $allowedGroups = \Server::get('conf_allowedgroup');
                if (in_array($corporationName, $allowedGroups)) {
                    return self::createGroup('corporation', $corporationName, $corporationID);
                }
            }
            return 0;            
        }
    }
    
    public static function getAllyGroupID($allianceName,$allianceID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT groupID FROM '.$prefix.'group WHERE type=\'alliance\' AND eveID = :allianceID';
        $row = \Db\Sql::queryRow($query,[':allianceID'=>$allianceID]);
        if (!empty($row)) {
            return $row['groupID'];
        }else{
            if (\Server::get('conf_usewhitelist')==true) {
                $allowedGroups = \Server::get('conf_allowedgroup');
                if (in_array($allianceName, $allowedGroups)) {
                    return self::createGroup('alliance', $allianceName, $allianceID);
                }
            }
            return 0;
        }
    }
    
    protected static function createGroup($type,$title,$eveID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'INSERT INTO '.$prefix.'group (type,title,eveID) VALUES (:type, :title, :eveID)';
        $id = \Db\Sql::execute($query, [
            ':type'=>$type,
            ':title'=>$title,
            ':eveID'=>$eveID
        ], true
                );
        return $id;
    }
}
