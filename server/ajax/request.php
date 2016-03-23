<?php


//Initialize some stuff
require_once '../server.php';
Server::start();

//get POST data
if (isset($_POST['m'])) {
    $messageJson = $_POST['m'];
}else{
    $messageJson = null;
}

if (isset($_POST['l'])) {
    $lastMessageID = $_POST['l'];
}else{
    $lastMessageID = 0;
}
$messages = json_decode($messageJson,true);

//go through all messages
if ($messages!==null) {
    foreach($messages as $message) {
        if (isset($message['action']) AND isset($message['data'])) {
            $action = $message['action'];
            $data = [$message['data']];
            $callback = ['Action\\'.$action,'Execute'];
            call_user_func_array($callback, $data);                
        }
    }
}

//check if we changed system or shiptype
if (\Controller\ClientController::hasJumped()){
    $fromSolarSystemID = \Controller\SessionController::session()->solarSystemID();
    $toSolarSystemID = Lib\IgbApi::getHeaderSolarSystemID();
    if (isset($_POST['t'])) {
        $tabID = filter_var($_POST['t'],FILTER_SANITIZE_NUMBER_INT);
        $fromMapSystem = new \Model\MapSystem($fromSolarSystemID);
        $toMapSystem = new \Model\MapSystem($toSolarSystemID);
        //check that the systems don't have a gate connection and exist
        if ($fromMapSystem->solarSystemClass()!=null 
                AND $toMapSystem->solarSystemClass()!=null 
                AND ! \Controller\MapSystemController::haveGateConnection($fromMapSystem, $toMapSystem)) {
            //make sure we have acccess to the tab
            $tab = \Controller\ClientController::returnAllowedTab($tabID);
            if ($tab!= null) {
                //create the connection (does nothing if the connection already exists)
                \Controller\TabController::makeConnection($fromMapSystem, $toMapSystem, $tab);
                $pilot = new \Model\Pilot(\Lib\IgbApi::getHeaderCharname(), \Lib\IgbApi::getHeaderShipTypeName(),$toSolarSystemID);
                //set session
                $pilot->saveToSession();
                
                //inform other clients
                \Controller\ClientController::pushPilotToPipe($pilot);
            }
        }else if($toMapSystem->solarSystemClass()!=null) {
            $pilot = new \Model\Pilot(\Lib\IgbApi::getHeaderCharname(), \Lib\IgbApi::getHeaderShipTypeName(),$toSolarSystemID);
                //set session
                $pilot->saveToSession();                
                //inform other clients
                \Controller\ClientController::pushPilotToPipe($pilot);
        }
    }
    \Server::sendClientAction('spssi', ['solarSystemID'=>$toSolarSystemID]);
}else if (\Controller\ClientController::hasChangedShipType()) {
    //get active tab ID
    $tabID = filter_var($_POST['t'],FILTER_SANITIZE_NUMBER_INT);
    
    //make sure we have acccess to the tab
    $tab = \Controller\ClientController::returnAllowedTab($tabID);
    if ($tab!= null) {
        //create pilot model
        $pilot = new \Model\Pilot(\Lib\IgbApi::getHeaderCharname(), \Lib\IgbApi::getHeaderShipTypeName(), \Lib\IgbApi::getHeaderSolarSystemID());

        //set session
        $pilot->saveToSession();
        
        //inform other clients
        \Controller\ClientController::pushPilotToPipe($pilot);
    }
}

//unload offline pilots
\Controller\TabController::unloadPilots();

//get pipe messages
\Controller\PipeController::loadPipeMessages($lastMessageID);
$highestMessageID = -1;
while($pipeMessage = \Controller\PipeController::getNextPipeMessage()) {
    \Server::sendClientAction($pipeMessage->action(), $pipeMessage->data());
    //set hightest message ID (if pipe messageID is higher)
    $highestMessageID = $pipeMessage->messageID()>$highestMessageID? $pipeMessage->messageID():$highestMessageID;
}
if ($highestMessageID!==-1) {
    \Server::sendClientAction('setMessageID', ['id'=>$highestMessageID]);
}


Server::end();