<?php



namespace Controller;

class PipeController extends BaseController {
    protected static $pipemessages;
    protected static $messagePointer = 0;
    
    /**
     * returns the next pipemessage,
     * pipe needs to be loaded first
     * @return \Model\PipeMessage
     */
    public static function getNextPipeMessage() {
        if (self::$pipemessages!==null) {
            if (isset(self::$pipemessages[self::$messagePointer])) {
                self::$messagePointer++;
                return self::$pipemessages[self::$messagePointer-1];
            }
        }
        return false;
            
    }
    
    /**
     * loads messages from the pipe, starting from the $lastMessageID
     * @param integer $lastMessageID
     */
    public static function loadPipeMessages($lastMessageID) {
        self::garbageCollector(\Server::get('conf_pipeexpire'));
        
        $openTabIDs = \Controller\ClientController::returnOpenTabIDs();        
        if (empty($openTabIDs)) {
            $openTabIDs = [0];
        }
        $accountID = \Controller\SessionController::session()->accountID();
                
        $prefix = \Db\Sql::dbPrefix();
        $queryTabIDs = implode(',',$openTabIDs);
        $query = 'SELECT p.* FROM '.$prefix.'pipe as p, '.$prefix.'groupmember as m WHERE '
                . 'p.messageID>:lastMessageID AND '
                . '( (p.tabID = 0 AND (p.groupID = m.groupID AND m.accountID = :accountID)) OR '
                . '(p.tabID IN ('.$queryTabIDs.'))) GROUP BY p.messageID ORDER BY p.messageID ASC';
        $result = \Db\Sql::query($query,[
            ':accountID'=>$accountID,
            ':lastMessageID'=>$lastMessageID
        ]);
        
        if (!empty($result)) {
            foreach($result as $messageAssoc) {
                self::$pipemessages[] = new \Model\PipeMessage($messageAssoc);
            }           
        }        
    }
    
    /**
     * returns the highest messageID that's currently in the pipe,
     * returns 0 if pipe is empty
     * @return integer
     */
    public static function getHighestPipeMessageID() {
        $prefix = \Db\Sql::dbPrefix();
        $query = 'SELECT messageID FROM '.$prefix.'pipe ORDER BY messageID DESC LIMIT 1';
        $row = \Db\Sql::queryRow($query);
        if (!empty($row)) {
            return $row['messageID'];
        }else{
            return 0;
        }
    }
    /**
     * sends a message to clients that have the tabID open
     * @param integer $tabID
     * @param string $action
     * @param array $data
     */
    public static function sendTabMessage($tabID,$action,$data) {
        $groupID = 0;
        self::sendMessage($tabID, $groupID, $action, $data);
    }
    
    /**
     * sends a message to clients that belong to the groupID
     * @param integer $groupID
     * @param string $action
     * @param array $data
     */
    public static function sendGroupMessage($groupID,$action,$data) {
        $tabID = 0;
        self::sendMessage($tabID, $groupID, $action, $data);
    }
    
    /**
     * sends a message to the pipe
     * @param integer $tabID
     * @param integer $groupID
     * @param string $action
     * @param array $data
     */
    public static function sendMessage($tabID,$groupID,$action,$data) {
        $time= time();
        $message = new \Model\PipeMessage();
        $message->tabID($tabID);
        $message->groupID($groupID);
        $message->action($action);
        $message->data($data);
        $message->creationTime($time);
        //send it
        $message->save();
    }
    
    /**
     * deletes pipemessages that are older than $expiretime
     * @param integer $expiretime
     */
    public static function garbageCollector($expiretime) {
        $prefix = \Db\Sql::dbPrefix();
        $mintime = time()-$expiretime;
        
        $query = 'DELETE FROM '.$prefix.'pipe WHERE creationTime<:mintime';
        \Db\Sql::execute($query,[
            ':mintime'=>$mintime
        ]);
    }
    
}
