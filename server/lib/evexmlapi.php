<?php

namespace Lib;

class EveXmlApi {
    public static function isCorp($id) {        
        $id = filter_var($id,FILTER_SANITIZE_NUMBER_INT);
        if ($id==0) {
            return false;
        }
        $useragent='JSig3';
        $ch = curl_init();
        $lookup_url="https://api.eveonline.com/corp/CorporationSheet.xml.aspx?corporationID=".$id;
        curl_setopt($ch, CURLOPT_URL, $lookup_url);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result===false) {            
            return false;
        }
         $xml=simplexml_load_string($result);
        if (isset($xml->result->corporationID)) {
            return true;
        }
        return false;
        
    }
    public static function getEveID($stuff) {
        $stuff=self::sanitizeEVEString($stuff);
        $clean = preg_replace('/(\'|&#0*39;)/', "%27", $stuff);
        $clean = preg_replace('/\s+/',"%20",$stuff);
        $useragent='JSig3';
        $ch = curl_init();
        $lookup_url="https://api.eveonline.com/eve/CharacterID.xml.aspx?names=".$clean;
        curl_setopt($ch, CURLOPT_URL, $lookup_url);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result===false) {
            return false;
        }else{
            $xml=simplexml_load_string($result);
            if (!isset($xml->result->rowset->row)) {
                return false;
            }
            $atributes = $xml->result->rowset->row->attributes();
            if (isset($atributes['characterID'])) {
                return (int) $atributes['characterID'];
            }            
            return 0;
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
}
