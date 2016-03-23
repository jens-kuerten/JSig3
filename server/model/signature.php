<?php

namespace Model;
class Signature extends BaseModel{
    protected $tableName = 'signature';
    protected $assoc = [];
    
    public function __construct($param=null,$solarSystemID = 0, $isAnom = false,$type='', $detail = '',$groupID=0) {
        if (is_array($param)) {
            $this->loadFromAssoc($param);
        }else{
            $this->assoc['solarSystemID']=$solarSystemID;
            $this->assoc['sig']=$param;
            $this->assoc['groupID']=$groupID;
            $this->assoc['isAnom']=$isAnom;
            if ($type!=='') {
                $this->assoc['type'] = $type;
            }
            if ($detail!=='') {
                $this->assoc['detail'] = $detail;
            }
        }
    }
    
    /**
     * save to database, on duplicate key update
     * @return boolean
     */
    public function save() {
        $this->assoc['timeCreated']=time();
        return $this->writeToDb();
    }
    
    public function isOld($isOld = null) {
        if ($isOld!==null) {
            $this->assoc['isOld']=$isOld;
        }else{
            return $this->returnProperty('isOld');
        }
    }
    
    public function isAnom() {
        return $this->returnProperty('isAnom');
    }
    
    public function sig($sig=null) {
        if ($sig!= null) {
            $this->assoc['sig']=$sig;
        }else{
            return $this->returnProperty('sig');
        }
    }
}
