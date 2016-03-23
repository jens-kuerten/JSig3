<?php

class Server {

    /**
     * contains global key=>value pairs
     * @var array 
     */
    protected static $globals = [];                
    
    /**
     * contains messages that get send to the client as JSON 
     * on Server end();
     * @var array 
     */
    protected static $returnMessages = array();
    
    /**
     * adds a message that gets send to client on server::end()
     * @param array $message
     * @return boolean
     */
    public static function addReturnMessage($message) {
        self::$returnMessages[] = $message;
        return true;
    }
    
    public static function sendClientAction($action,$data) {
        self::addReturnMessage(['a'=>$action,'d'=>$data]);
    }
    
    /**
     * initializes stuff
     */
    public static function start() {
        self::autoloader();
        self::loadConfig();
        self::loadSecrets();           
        set_error_handler('Server::errorHandler');
        Controller\SessionController::start();
    }
    
    /**
     * outputs JSON of the returnMessages
     */
    public static function end() {        
        echo json_encode(['m'=>self::$returnMessages]);        
    }

    /**
     * sets a global variable
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        self::$globals[$key] = $value;
        return true;
    }

    /**
     * returns a global variable
     * @param string $key
     * @return mixed
     */
    public static function get($key) {
        if (isset(self::$globals[$key])) {
            return self::$globals[$key];
        }
        return false;
    }

    /**
     * start autoloader
     */
    protected static function autoloader() {
        spl_autoload_register(function ($class_name) {               
            $class_name = strtolower(str_replace("\\", "/", $class_name));
            $baseDir = dirname(__FILE__);            
            $path       = $baseDir.'/'.$class_name.".php";
            if (file_exists($path)) {
                require_once($path);
            } else {                
                die("The file {$path}.php could not be found!");
                }              
        });              
    }

    protected static function loadConfig() {
        $configArray = parse_ini_file('config/config.ini');
        foreach ($configArray as $key => $value) {
            self::$globals['conf_' . $key] = $value;
        }
    }

    protected static function loadSecrets() {
        $secretArray = parse_ini_file('config/secrets.ini');
        foreach ($secretArray as $key => $value) {
            self::$globals['secret_' . $key] = $value;
        }
    }
    
    public static function errorHandler($number, $msg, $file, $line, $vars) {
        $msg = "$msg in file $file on line $line";
        $jsonerror = json_encode(['m'=>[['a'=>'error','d'=>['message'=>$msg]]]]);
        echo $jsonerror;
        //print_r(debug_backtrace());
        die();
    }
}

?>