<?php
//Initialize some stuff
require_once 'server/server.php';
Server::start();

if (!isset($_POST['regusername']) OR !isset($_POST['regpassword'])) {    
    echo "you shouldn't be here";
    die();
}
$_SESSION['regusername'] = $_POST['regusername'];
$_SESSION['regpassword'] = $_POST['regpassword'];

\Lib\SSO::redirect('register');