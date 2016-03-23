<?php


namespace Action;
class CreatePoi extends BaseAction{
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $groupID = self::returnDataVariable('groupID');
        $systemName = self::returnDataVariable('solarSystemName');
        $description = self::returnDataVariable('description');
        $type = self::returnDataVariable('type');
        $accountID = \Controller\SessionController::session()->accountID();
        
        $allowedGroups = \Controller\ClientController::returnGroups();
        
        //check if the groupID is in the list of allowed groups
        $found = false;
        foreach ($allowedGroups as $allowedGroup) {
            if ($allowedGroup->groupID() == $groupID) {
                $found=true;
                break;
            }
        }
        if ($found==false) {            
            return false;
        }
        
        //get the mapSystem
        $mapSystem = new \Model\MapSystem($systemName);
        if ($mapSystem->solarSystemID()!==0 AND $mapSystem->solarSystemID()!==null) {
            $newPoi = \Controller\PoiController::createPoi($mapSystem, $type, $description, $accountID, $groupID);            
            \Controller\PipeController::sendGroupMessage($groupID, 'addPoi', $newPoi->returnAssoc());
        }
    }
}
