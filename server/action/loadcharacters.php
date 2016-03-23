<?php


namespace Action;
class LoadCharacters extends BaseAction{
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $characters = \Controller\ClientController::returnCharacters();
        $characterArray=[];
        if (!empty($characters)) {
            foreach($characters as $character) {
                $characterArray[] = $character->returnAssoc();
            }
        }
        \Server::sendClientAction('loadcharacters', $characterArray);
    }
}
