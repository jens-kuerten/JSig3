<?php


namespace Model;
class Pilot {
    protected $assoc=[];
    
    public function __construct($characerName,$shipTypeName,$solarSystemID) {
        $this->assoc['characterName']=$characerName;
        $this->assoc['shipTypeName']=$shipTypeName;
        $this->assoc['solarSystemID']=$solarSystemID;
    }
    
    public function returnAssoc(){
        return $this->assoc;
    }
    
    public function saveToSession() {
        $session = \Controller\SessionController::session();
        $session->characterName($this->assoc['characterName']);
        $session->shipTypeName($this->assoc['shipTypeName']);
        $session->solarSystemID($this->assoc['solarSystemID']);
        return true;
    }
}
