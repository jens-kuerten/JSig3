<?php


namespace Action;
class CreateTab extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();        
        self::$data = $data;
        
        $groupID = self::returnDataVariable('groupID');
        
        $allowedGroups = \Controller\ClientController::returnGroups();
        
        //check if the groupID is in the list of allowed groups
        $found = false;
        $groupName = '';
        $imgURL = '';
        foreach ($allowedGroups as $allowedGroup) {
            if ($allowedGroup->groupID() == $groupID) {
                $groupName = $allowedGroup->title();
                $groupType = $allowedGroup->type();
                $groupEveID = $allowedGroup->eveID();
                if ( $groupType=='corporation') {
                    $imgURL = 'https://image.eveonline.com/Corporation/'.$groupEveID.'_64.png';
                }else if($groupType=='alliance') {
                    $imgURL = 'https://image.eveonline.com/Alliance/'.$groupEveID.'_64.png';
                }
                $found=true;
                break;
            }
        }
        if ($found==false) {            
            return false;
        }
        
        $tab = new \Model\Tab();
        $tab->groupID($groupID);
        $tab->tabName($groupName);
        $tab->imgURL($imgURL);
        $tab->save();
        
         $data = [$tab->returnAssoc()];
         \Controller\PipeController::sendGroupMessage($groupID, 'addAllowedTabs', $data);
         if (\Controller\ClientController::openTab($tab->tabID())) { //add tab to list of open tab, then push it to the client
            \Controller\TabController::pushTabToClient($tab);            
        }
    }
}
