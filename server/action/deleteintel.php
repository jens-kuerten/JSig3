<?php



namespace Action;
class DeleteIntel extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        
        self::$data = $data;
        $id = self::returnDataVariable('id');
        $groupID = self::returnDataVariable('groupID');
        
        \Controller\IntelController::deleteIntel($id, $groupID);
    }
}
