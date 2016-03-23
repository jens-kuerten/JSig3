<?php
//Initialize some stuff
require_once 'server/server.php';
Server::start();

$characterID = \Lib\SSO::handleCallback();
$character = new Model\eveCharacter($characterID);



if ($_SESSION['sessionState']==='register') {
    $regusername = $_SESSION['regusername'];
    $regpassword = $_SESSION['regpassword'];
    $client = new \Model\Client();
    $client->userName($regusername);
    $passwordHash = password_hash($regpassword,PASSWORD_BCRYPT);
    $client->passwordHash($passwordHash);
    $client->mainCharacterName($character->characterName());
    
    if (!$client->create()) {
        //account already exists
        header('Location: index.php?state=regexists');
        die();
    }
    $client = new \Model\Client($regusername, true);
    
    $accountID = $client->accountID();
    $character->accountID($accountID);
    $character->save();
    header('Location: index.php?state=regsuccess');
}else if($_SESSION['sessionState']==='add'){    
    $sessionType = \Controller\SessionController::session()->sessionType();
    if ($sessionType>0) {            
        }else{
            trigger_error('not logged in');
        }
    
    $accountID = \Controller\SessionController::session()->accountID();    
    $character->accountID($accountID);
    $character->save();
    \Controller\ClientController::updateGroups();
    header('Location: map.php'); 
    echo "something<br>";
}else{
    echo $_SESSION['sessionState'].'uhm...';
}