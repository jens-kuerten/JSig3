<?php

namespace Lib;

class IgbApi {
    /**
     * returns true if the eve trusted header is set to yes
     * @return boolean
     */
    public static function isTrusted() {
        if (self::getHeader('EVE_TRUSTED')==='Yes') {
            return true;
        }
        else if(self::getHeader('Eve-Trusted')==='Yes') {
            return true;
        }
        else {
            return false;            
        }
    }
    
    /**
     * returns (sanitized) charactername from the EVE header, returns false when not set
     * @return string
     */
    public static function getHeaderCharname() {
        if (self::isTrusted()) {
            if ($charname =  self::getHeader('EVE_CHARNAME')) return self::sanitizeEVEString($charname);
            else if($charname = self::getHeader('Eve-Charname')) return self::sanitizeEVEString($charname);
            else return false;
        }
    }
    
    /**
     * returns solarSystemID from the EVE header, returns false when not set
     * @return string
     */
    public static function getHeaderSolarSystemID() {
        if (self::isTrusted()) {
            if ($solarSystemID =  self::getHeader('EVE_SOLARSYSTEMID')) return (int)$solarSystemID;
            else if($solarSystemID = self::getHeader('Eve-Solarsystemid')) return (int)$solarSystemID;
            else return false;
        }
    }
    
    /**
     * returns charactername from the EVE header, returns false when not set
     * @return string
     */
    public static function getHeaderShipTypeName() {
        if (self::isTrusted()) {
            if ($shipTypeName =  self::getHeader('EVE_SHIPTYPENAME')) {
                return self::sanitizeEVEString($shipTypeName);
            }    
            else if($shipTypeName = self::getHeader('Eve-Shiptypename')) {
                return self::sanitizeEVEString($shipTypeName);
                
            }
            else return false;
        }
    }
    
    /**
     * removes characters except for a-Z0-9_'\w\-
     * use for solarsystems,characters,items,etc...
     * @param string $string string from the EVE header
     * @return string illegal characters removed
     */
    public static function sanitizeEVEString($string){
        return preg_replace( "/[^a-zA-Z0-9_'\W\-]/", "", $string );
    }     
    
     /**
     * returns a header variable from the client
     * @param sring $header
     * @return string
     */
    protected static function getHeader($header) {       
        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if ($requestHeaders){
                if (isset($requestHeaders[$header])) {
                    return self::sanitizeEVEString($requestHeaders[$header]);
                }else{
                    return false;
                }		
            }    
        }else{
            if (isset($_SERVER['HTTP_'.$header])){
                return self::sanitizeEVEString( $_SERVER['HTTP_'.$header]);
            }else{
                return false;
            }		
        }     
    }
}
