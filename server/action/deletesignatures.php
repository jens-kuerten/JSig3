<?php



namespace Action;
class DeleteSignatures extends BaseAction {
    public static function Execute($data) {
        self::requireLogin();
        self::$data = $data;
        
        $solarSystemID = self::returnDataVariable('solarSystemID');
        $tabID = self::returnDataVariable('tabID');
        $sigs = self::returnDataVariable('sigs');
        
        $tab = \Controller\TabController::returnOpenTab($tabID);
        
        if ($tab!=null) {
            foreach ($sigs as $sig) {
                \Controller\SignatureController::deleteSignature($sig, $solarSystemID, $tab);
            }
        }
    }
}
