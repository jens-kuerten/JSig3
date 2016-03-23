<?php
require_once 'server/server.php';
Server::start();
if(isset($_COOKIE['JSIG3Token'])) {    
    if (\Controller\ClientController::checkToken($_COOKIE['JSIG3Token'])) {
        header('Location: map.php');
    }
} 

$state='';
if (isset($_GET['state'])) {
    $state = $_GET['state'];
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>JSig</title>        
        <SCRIPT type="text/javascript" SRC="js/lib.js"></SCRIPT>
        <SCRIPT type="text/javascript" SRC="js/index.js?v=<?php  ?>"></SCRIPT>                        
        <LINK rel="stylesheet" type="text/css" href="css/map.css" />
        
        <link rel="apple-touch-icon" sizes="57x57" href="img/ico/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="img/ico/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="img/ico/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="img/ico/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="img/ico/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="img/ico/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="img/ico/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="img/ico/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="img/ico/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="img/ico/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="img/ico/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="img/ico/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="img/ico/favicon-16x16.png">
<link rel="manifest" href="img/ico/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="img/ico/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
        
    </head>    
<body onLoad="Init('<?php echo $state?>')">
    <div id="topbar">
        <div id="logobox">
            <img src="img/logo.png" height=auto width=120px>
            <div id="version">V<?php echo Server::get('conf_version');?></div>  
        </div>        
    </div>
    <div id="registerbox" class="loginbox">
        
    </div>
    <div id="loginbox" class="loginbox" style="display:none">
               
    </div>
    <div id="notrust" class="loginbox" style="display:none">
        <h1>IGB trust needed</h1>
        <b>Please use the ingame browser, trust the website and <a href="index.php">reload</a>.</b>
    </div>
    <div id="errorscreen" class="errorscreen">
        <div id="errormessage" class="errormessage">
            
        </div>
    </div>