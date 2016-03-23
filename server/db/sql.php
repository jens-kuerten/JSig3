<?php

namespace Db;
use PDO;

class Sql {
    /**     
     * @var PDO $pdo contains the pdo object, is null when database connection not opened yet
     */
    protected static $pdo = null;
    
    public static $querynumber = 0;
    public static $querys='';
    
    /**
     * creates and returns a PDO object
     * @static    
     * @return PDO
     */        
    protected static function getPDO(){        
        $dbHost = \Server::get('secret_dbHost');
        $dbName = \Server::get('secret_dbName');
        $dbUser = \Server::get('secret_dbUser');
        $dbPassword = \Server::get('secret_dbPassword');        
        $dsn = "mysql:host=$dbHost;dbname=$dbName";
        //check if we have a database connection open
        if (!self::$pdo != null) {
            try {
                //open new connection and store it as static variable
                self::$pdo = new \PDO($dsn,$dbUser,$dbPassword);
            } catch (Exception $ex) {
                throw new Exception('Unable to connect to database');
            }            
        }
        
        //return connection
        return self::$pdo;
    }
    
    public static function dbPrefix() {
        return \Server::get('secret_dbPrefix');
    }
    
    /**
     * executes a query and returns the number of affected rows or the last insert id
     * use for inserts, updates, etc...
     * @static
     * @param string $query  query
     * @param array $parameters parameters for the query
     * @param boolean $returnInsertID return last insert ID instead of rows affected (use for inserts)
     * @return int number of affected rows
     */
    public static function execute($query, $parameters = array(), $returnInsertID = false) {
        //open a connection to the database
        $pdo = self::getPDO();
        
        //begin the transaction (needed for rollback)
        $pdo->beginTransaction();
        
        //prepare the query
        $stmt = $pdo->prepare($query);                
        
        //execute the query with parameters
        $stmt->execute($parameters);
        
        //check if an error occured
        if($stmt->errorCode() != 0) {
            //roll back changes to the database
            $pdo->rollBack();
            //trigger error message
            $errorInfo = $stmt->errorInfo();            
            trigger_error($errorInfo[2].$query);
        }                
        
        //get last insert ID
        $lastInsertID = $returnInsertID ? $pdo->lastInsertId() : 0;
        
        //commit query
        $pdo->commit();
        
        //get number of affected rows
        $rowCount = $stmt->rowCount();
                
        //close cursor
        $stmt->closeCursor();
        
        if ($rowCount===0) {
            $lastInsertID=false;
        }
        
        //return last insert id if requested
        if ($returnInsertID) {
            return $lastInsertID;
        }
        self::$querynumber++;
        self::$querys .=$query.'<br>';
        //otherwise return affected rows
        return $rowCount;                
    }
    
    /**
     * executes a query and returns the result as an associative array
     * use for SELECT
     * @static
     * @param string $query query
     * @param array $parameters parameters for the query
     * @return array associative array of the results
     */
    public static function query($query, $parameters = array()) {               
        try{
            //open a connection to the database
            $pdo = self::getPDO();
            
            //prepare the query
            $stmt = $pdo->prepare($query);
            
            //execute the query with parameters
            $stmt->execute($parameters);
            
            //check if an error occured
            if($stmt->errorCode() != 0) {
                //trigger error message
                $errorInfo = $stmt->errorInfo();
                trigger_error($errorInfo[2]);
            }
            
            //fetch the result as associative array
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
            //close cursor
            $stmt->closeCursor();
            self::$querynumber++;
            self::$querys .=$query.'<br>';
            //return the result
            return $result;            
        } catch (Exception $ex) {
            trigger_error($ex);
        }
    }
    
     /**
     * executes a query and returns a single result as an associative array
     * use for SELECT
     * @static
     * @param string $query query
     * @param array $parameters parameters for the query
     * @return array associative array of the results
     */
    public static function queryRow($query, $parameters = array()) {
        //get the result
        $result = self::query($query, $parameters);
        
        //check if there is a row
        if (isset($result[0])) {
            //return the first result
            return $result[0];
        }else{
            //no result, return empty array
            return array();
        }
    }
}
