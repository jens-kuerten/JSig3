<?php


namespace Action;

class Login extends BaseAction {   
    public static function Execute($data) {
        self::$data = $data;        
        $username = self::returnDataVariable('username');
        $password = self::returnDataVariable('password');
        $remember = self::returnDataVariable('remember');
        if ($username===null OR $password===null) {            
            return false;
        }
        
        //try to login
        if (\Controller\ClientController::checkLogin($username, $password)) {
            //login succeeded
            if ($remember==true) {
                $accountID = \Controller\SessionController::session()->accountID();
                $token = self::createToken($accountID);
                if ($token!==false) {
                    setcookie('JSIG3Token', $token, time() + (86400 * 30), "/"); // 86400 = 1 day
                }                
            }
            self::sendClientAction('loginstatus', ['status'=>true]);
            return true;
        }else{
            self::sendClientAction('loginstatus', ['status'=>false]);
            return false;
        }
    }
    
    protected static function returnNewToken() {
        $secret = time();
        for ($i = 0; $i < 20; ++$i) {
            $secret .= chr(mt_rand(0, 255));
        }
        return md5($secret);
    }
        
    public static function updateToken($oldtoken,$accountID) {        
        $dbPrefix = \Db\Sql::dbPrefix();        
        
        $token = self::returnNewToken();
        $query = 'UPDATE '.$dbPrefix.'token SET token=:token , timeCreated=:time WHERE accountID = :accountid AND token=:oldtoken';
        $rowCount = \Db\Sql::execute($query,array(
            ":oldtoken"=> $oldtoken,
            ":token"=> $token,
            ":time"=>time(),
            ":accountid"=> $accountID
        ));        
        if ($rowCount>0) {
            return $token;
        }
        return false;
    }
    
     public static function createToken($accountID) {
        $dbPrefix = \Db\Sql::dbPrefix();        
        
        $token = self::returnNewToken();
        $query = 'INSERT INTO '.$dbPrefix.'token (token, timeCreated, accountID) VALUES '
                . '(:token,:time,:accid)';
        $rowCount =\Db\Sql::execute($query,array(
            ":token"=>$token,
            ":time"=>time(),
            ":accid"=>$accountID
        ));
        
        if ($rowCount>0) {
            return $token;
        }
        return false;
    }
}
