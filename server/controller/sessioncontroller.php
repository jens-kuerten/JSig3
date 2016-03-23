<?php

/*
 * Copyright (C) 2015 Jens
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of session
 *
 * @author Jens
 */
namespace Controller;
use Model;

class SessionController{
    /**
     *
     * @var \Model\Session
     */
    protected static $model;
    
    public static function start() {                
        session_set_save_handler(
            array('Controller\SessionController', "_open"),
            array('Controller\SessionController', "_close"),
            array('Controller\SessionController', "_read"),
            array('Controller\SessionController', "_write"),
            array('Controller\SessionController', "_destroy"),
            array('Controller\SessionController', "_gc")
        );
        
        session_start();
    }
    
    public static function _open() {
        return true;
    }
    public static function _close() {
        return true;
    }
    
    public static function _read($key) {
        self::$model = new \Model\Session($key);
        return self::$model->serialData();
    }
    
    public static function _write($key,$data) {
        self::$model->sessionKey($key);
        self::$model->serialData($data);
        self::$model->accessTime(time());
        self::$model->save();
    }
    
    public static function _destroy($key) {
        return true;
    }
    
    public static function _gc() {
        return true;
    }
    
    /**
     * returns the session model
     * @return \Model\Session
     */
    public static function session() {
        return self::$model;
    }
}
