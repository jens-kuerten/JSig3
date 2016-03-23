<?php

namespace Controller;
class SignatureController extends BaseController{
    
    /**
     * returns an array of signatures of the solarsystemID from the tabID
     * @param integer $solarSystemID
     * @param integer $tabID
     * @return array<\Model\Signature>
     */
    public static function returnSignatures($solarSystemID,$tabID) {
        $signatures = [];
        
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT s.* FROM '.$prefix.'signature as s,'.$prefix.'tab as t '
                . 'WHERE s.groupID = t.groupID AND t.tabID = :tabID AND s.solarSystemID = :solarSystemID';
        $result = \Db\Sql::query($query,[
            ':solarSystemID'=>$solarSystemID,
            ':tabID'=>$tabID
        ]);
        if (!empty($result)) {
            foreach($result as $signatureAssoc) {
                $signatures[] = new \Model\Signature($signatureAssoc);
            }
        }
        return $signatures;
    }
    
    public static function addSigPaste($solarSystemID,\Model\Tab $tab,$paste,$showAnoms) {
        $prefix = \Db\Sql::dbPrefix();
        
        //get list of current signatures in that system
        $oldSignatures = self::returnSignatures($solarSystemID, $tab->tabID());
        
        //set all the old sigs as old, but only if the sigpaste contains more than one system
        if (count($paste)!=1) {
            foreach ($oldSignatures as $key=>$oldSig) {
                $oldSig->isOld(true);
                if ($oldSig->isAnom()==true AND $showAnoms==false) {
                    unset($oldSignatures[$key]);
                }
            }    
        }
        
        
        $newSignatures = [];
        foreach($paste as $sigpaste) {
            $signature = new \Model\Signature($sigpaste['sig'],$solarSystemID,$sigpaste['isAnom'],$sigpaste['type'],$sigpaste['detail'],$tab->groupID());
            $saved = $signature->save();            
            //check if the sig is in the list of old Sigs
            $found = false;
            foreach ($oldSignatures as $key=>$oldSig) {                
                if ($oldSig->sig()==$signature->sig()) {
                    if ($saved === false) {
                        //unset($oldSignatures[$key]);
                        $oldSignatures[$key]->isOld(false);
                    }else{
                        //replace it                    
                        $oldSignatures[$key] = $signature; 
                        $oldSignatures[$key]->isOld(false);                        
                    }
                    $found = true;
                    break;
                }
            }            
            if ($found ===false) { //if it isn't add it
                $oldSignatures[] = $signature;
            }
        }
        
        //send the updated signatures to the client
        foreach($oldSignatures as $updatedSig) {
            $assoc = $updatedSig->returnAssoc();
            //should be already set, but just to be sure...
            $assoc['groupID'] = $tab->groupID();
            $assoc['solarSystemID'] = $solarSystemID;
            //push it
            \Controller\PipeController::sendGroupMessage($tab->groupID(), 'addSig', $assoc);
        }
        \Server::sendClientAction('opensigwindow', ['solarSystemID'=>$solarSystemID,'tabID'=>$tab->tabID()]);
        
    }
    
    public static function deleteSignature($sig,$solarSystemID ,\Model\Tab $tab) {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'DELETE FROM '.$prefix.'signature WHERE '
                . 'sig = :sig and solarSystemID = :solarSystemID AND groupID = :groupID';
        $rowCount = \Db\Sql::execute($query,[
            ':sig'=>$sig,
            ':solarSystemID'=>$solarSystemID,
            ':groupID'=>$tab->groupID()
        ]);
        
        if ($rowCount>0) {
            \Controller\PipeController::sendGroupMessage($tab->groupID(), 'deleteSig', [
                'solarSystemID'=>$solarSystemID,
                'groupID'=>$tab->groupID(),
                'sig'=>$sig
                ]);
            return true;
        }
        return false;
    }
}
