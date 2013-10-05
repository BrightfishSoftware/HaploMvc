<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploNonce
 **/

namespace HaploMvc;

/**
 * Class HaploNonce
 * @package HaploMvc
 */
class HaploNonce extends HaploSingleton {
    /**
     * @var string
     */
    protected $secret;
    /**
     * @var string
     */
    protected $name;

    /**
     * @param HaploApp $app
     * @return mixed
     */
    public static function get_instance(HaploApp $app = null) {
        $class = get_called_class();

        if (!isset(self::$instances[$class]) && !is_null($app)) {
            self::$instances[$class] = new $class($app);
        }
        return self::$instances[$class];
    }

    /**
     * Class constructor - sets up variables and creates token
     *
     * @param HaploApp $app
     * @return HaploNonce
     */
    protected function __construct(HaploApp $app) {
        $this->secret = $app->config->get_key('nonce', 'secret');
        $this->name = $app->config->get_key('nonce', 'name');
        $this->create();
    }
    
    /**
     * Check token passed in request with last generated token and 
     * then create a new token for subsequent requests
     *
     * @return boolean
     **/
    public function check() {
        $result = (
            !empty($_SESSION[$this->name]) &&
            !empty($_REQUEST[$this->name]) && 
            $_SESSION[$this->name] == $_REQUEST[$this->name]
        );
        $this->create(true);
        
        return $result;
    }
    
    /**
     * Get the token
     *
     * @return string
     **/
    public function get() {
        return !empty($_SESSION[$this->name]) ? $_SESSION[$this->name] : false;
    }
    
    /**
     * Create a new token
     *
     * @param boolean $force Force creation of a new token even if one already exists
     * @return boolean
     **/
    protected function create($force = false) {
        if (empty($_SESSION[$this->name]) || $force) {
            $_SESSION[$this->name] = hash_hmac('sha512', uniqid(), $this->secret);
        
            return $_SESSION[$this->name];
        }
        
        return false;
    }
}