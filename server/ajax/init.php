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
            $callback = ['\Action\\'.$action,'Execute'];
            call_user_func_array($callback, $data);                
        }
    }
}

Server::end();