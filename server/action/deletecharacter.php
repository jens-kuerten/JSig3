<?php


namespace Action;
class DeleteCharacter extends BaseAction{
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $characterID = self::returnDataVariable('characterID');
        $accountID = \Controller\SessionController::session()->accountID();
        
        if (\Controller\ClientController::deleteCharacter($characterID, $accountID)) {
            \Action\LoadCharacters::Execute([]);
        }
    }
}
