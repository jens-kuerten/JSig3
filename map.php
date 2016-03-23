<?php


//Initialize some stuff
require_once 'server/server.php';
Server::start();
if (\Controller\SessionController::session()->sessionType()<1) {
    header('Location: index.php');
    trigger_error('not logged in');
}
Server::set('charactername', \Controller\SessionController::session()->characterName());

$baseDir = dirname(__FILE__);
$template = new \Lib\Template($baseDir.'/templates', 'map');

$template->display();