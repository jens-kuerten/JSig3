<?php

namespace Db;

class tables {
    
    public static $character = [
        'characterID',
        'accountID',
        'characterName',
        'corporationName',
        'corporationID',
        'allianceID',
        'allianceName',
        'cachedUntil'
    ];
    public static $signature = [
        'solarSystemID',
        'groupID',
        'sig',
        'isAnom',
        'type',
        'detail',
        'timeCreated'
    ];
    public static $poi = [
        'id',
        'groupID',
        'solarSystemID',
        'stationID',
        'solarSystemName',
        'x',
        'y',
        'z',
        'description',
        'accountID',
        'type',
        'maxJumps',
        'maxLy'
    ];
    public static $pipe = [
        'messageID',
        'tabID',
        'groupID',
        'action',
        'data',
        'creationTime'
    ];
    public static $intel = [
        'id',
        'groupID',
        'solarSystemID',
        'type',
        'value',
        'eveID',
        'creationTime'
    ];
    public static $connection = [
        'tabID',
        'fromSolarSystemID',
        'toSolarSystemID',
        'type',
        'massStage',
        'timeStage',
        'massPassed',
        'creationTime',
        'eolTime'
    ];
    public static $tab = [
        'tabID',
        'tabName',
        'groupID',
        'imgURL'
    ];
    public static $mapsystem = [
        'tabID',
        'solarSystemName',
        'solarSystemID',        
        'label',
        'regionName',
        'x',
        'y',
        'z',
        'security',
        'gates',
        'static1',
        'static2',
        'static3',
        'solarSystemClass',
        'solarSystemEffect',
        'mapX',
        'mapY',
        'home',
        'locked',
        'rallypoint'
    ];
    
    public static $group = [
        'groupID',
        'title',
        'permissionLevel',
        'hidden',
        'type',
        'eveID'
    ];
    
    public static $session = [
        'sessionID',
        'sessionKey',
        'accountID',
        'characterName',
        'sessionType',
        'solarSystemID',
        'shipTypeName',
        'access',
        'serialData'
    ];
    public static $account = [
        'accountID',
        'username',
        'mainCharacterName',
        'passwordHash',
        'active',
        'openTabIDs'
    ];
}
