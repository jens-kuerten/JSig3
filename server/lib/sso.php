<?php

namespace Lib;

class SSO {
    protected static $userAgent = 'JSig3';
    
    public static function redirect($sessionState) {
        $SSOclientID =  \Server::get('secret_SSOclientID');
        $SSOcallbackUrl = \Server::get('conf_SSOCallbackUrl');
        $authsite='https://login.eveonline.com/';
        $authurl='/oauth/authorize';
        $state=  session_id();
        $redirect_uri=$SSOcallbackUrl.'callback.php';
        $_SESSION['auth_state']=$state;
        $_SESSION['sessionState'] = $sessionState;
        session_write_close();
        header(
            'Location:'.$authsite.$authurl
            .'?response_type=code&redirect_uri='.$redirect_uri
            .'&client_id='.$SSOclientID.'&scope=&state='.$state
        );
        exit;
    }
    
    public static function handleCallback() {
        if (!isset($_SESSION['auth_state'])) {
            trigger_error('no session');
        }
        if (!isset($_GET['code'])OR !isset($_GET['state'])) {
            trigger_error('no callback code');
        }
                
        $state=$_GET['state'];        
        if ($state!==$_SESSION['auth_state']) {
            trigger_error('incorrect state');
        }
        
        $code=$_GET['code'];
        
        $auth_token = self::getAuthToken($code);
        $characterID = self::getCharID($auth_token);
        return $characterID;
    }
    
    protected static function getAuthToken($code) {        
        $SSOsecret = \Server::get('secret_SSOsecret');
        $SSOclientID =  \Server::get('secret_SSOclientID');
        $SSOverifyPeer = \Server::get('conf_SSOverifyPeer');
        
        $url='https://login.eveonline.com/oauth/token';        
        $header='Authorization: Basic '.base64_encode($SSOclientID.':'.$SSOsecret);
        $fields_string='';
        $fields=array(
                    'grant_type' => 'authorization_code',
                    'code' => $code
                );
        foreach ($fields as $key => $value) {
            $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSOverifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $result = curl_exec($ch);
        if ($result===false) {
            trigger_error(curl_error($ch));
            return false;
        }
        curl_close($ch);
        $response=json_decode($result);
        $auth_token=$response->access_token;
        
        return $auth_token;
    }
    
    protected static function getCharID($auth_token) {
        $SSOverifyPeer = \Server::get('conf_SSOverifyPeer');
        
        $verify_url='https://login.eveonline.com/oauth/verify';
        $header='Authorization: Bearer '.$auth_token;
        
        $ch = curl_init();               
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSOverifyPeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $result = curl_exec($ch);
        if ($result===false) {
            atrigger_error(curl_error($ch));
            return false;
        }
        curl_close($ch);
        $response=json_decode($result);
        if (!isset($response->CharacterID)) {
            trigger_error('No character ID returned');
            return false;
        }

        $characterID =  $response->CharacterID;
        
        return $characterID;
    }
}
