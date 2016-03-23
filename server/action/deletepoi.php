<?php



namespace Action;
class DeletePoi extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        $id = self::returnDataVariable('id');
        $groupID = self::returnDataVariable('groupID');
        
        if (\Controller\PoiController::deletePoi($id,$groupID)) {
            \Controller\PipeController::sendGroupMessage($groupID, 'deletePoi', ['id'=>$id]);
        }
    }
}
