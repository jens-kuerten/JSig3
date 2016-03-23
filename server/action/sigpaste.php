<?php

namespace action;
class SigPaste extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        
        self::$data = $data;
        
        $tabID = self::returnDataVariable('tabID');
        $solarSystemID = self::returnDataVariable('solarSystemID');
        $paste = self::returnDataVariable('paste');
        $anoms = self::returnDataVariable('showAnoms');
        
        $tab = \Controller\TabController::returnOpenTab($tabID);
        
        if ($tab!=null) {
            \Controller\SignatureController::addSigPaste($solarSystemID, $tab, $paste,$anoms);
        }
    }
}
