<?php

//Initialize some stuff
require_once 'server/server.php';
Server::start();

\Lib\SSO::redirect('add');
