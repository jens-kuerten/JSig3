<?php


namespace Model;

class eveCharacter extends BaseModel{
    protected $assoc = [];    
    protected $tableName = 'character';
    
    public function __construct($characterID) {
        if (is_numeric($characterID)) {
            if (!$this->loadFromDb($characterID)) {
                //character not in DB, load it from the xmlAPI
                return $this->loadFromAPi($characterID);
            }
        }else
        if (is_array ($characterID)) {
            $this->loadFromAssoc($characterID);
        }
    }
    
    public function accountID($accountID=null) {
        if ($accountID!=null) {
            $this->assoc['accountID']=$accountID;
        }else{
            return $this->returnProperty('accountID');
        }
        
    }
    
    protected function loadFromDb($characterID) {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT * FROM '.$prefix.'character WHERE characterID = :characterID AND cachedUntil>:time';   
        $row = \Db\Sql::queryRow($query,[
            ':characterID'=>$characterID,
            ':time'=>time() 
            ]);
        if (!empty($row)) {
            $this->assoc = $row;                        
            return true;
        }
        else return false;
    }
    
    public function characterName() {
        return $this->returnProperty('characterName');
    }
    
    public function allianceID() {
        return $this->returnProperty('allianceID');
    }
    
    public function allianceName() {
        return $this->returnProperty('allianceName');
    }
    
    public function corporationID() {
        return $this->returnProperty('corporationID');
    }
    public function corporationName() {
        return $this->returnProperty('corporationName');
    }
    


    protected function loadFromAPi($characterID) {
        $SSOverifyPeer = \Server::get('conf_SSOverifyPeer');
        
        $ch = curl_init();
        $lookup_url="https://api.eveonline.com/eve/CharacterAffiliation.xml.aspx?ids=".$characterID;
        curl_setopt($ch, CURLOPT_URL, $lookup_url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JSIG');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSOverifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        
        if ($result===false) {
            return false;
            //trigger_error('No such character on the API'.curl_error($ch));
        }
        curl_close($ch);
        $xml=simplexml_load_string($result);
        $attributes = $xml->result->rowset->row->attributes();        
        if (isset($attributes["characterID"])) {
            $this->assoc['corporationID']=(string)$attributes["corporationID"];
            $this->assoc['corporationName']=(string)$attributes["corporationName"];
            $this->assoc['allianceID']=(string)$attributes["allianceID"];
            $this->assoc['allianceName']=(string)$attributes["allianceName"];
            $this->assoc['characterName'] = (string)$attributes["characterName"];
            $this->assoc['characterID'] =(string)$attributes["characterID"];
            $this->assoc['cachedUntil'] = strtotime($xml->cachedUntil);
            $this->save();
        } else {
            trigger_error("No character details returned from API");
        }
        return true;
    }
}
