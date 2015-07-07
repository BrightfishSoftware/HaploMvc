<?php
namespace HaploMvc\Security;

use HaploMvc\App;

/**
 * Class Nonce
 * @package HaploMvc
 */
class Nonce
{
    /**
     * @var string
     */
    protected $secret;
    /**
     * @var string
     */
    protected $name;

    /**
     * Class constructor - sets up variables and creates token
     *
     * @param App $app
     * @return Nonce
     */
    public function __construct(App $app)
    {
        $this->secret = $app->config->getKey('nonce', 'secret');
        $this->name = $app->config->getKey('nonce', 'name');
        $this->create();
    }
    
    /**
     * Check token passed in request with last generated token and 
     * then create a new token for subsequent requests
     *
     * @return boolean
     **/
    public function check()
    {
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
    public function get()
    {
        return !empty($_SESSION[$this->name]) ? $_SESSION[$this->name] : false;
    }
    
    /**
     * Create a new token
     *
     * @param boolean $force Force creation of a new token even if one already exists
     * @return boolean
     **/
    protected function create($force = false)
    {
        if (empty($_SESSION[$this->name]) || $force) {
            $_SESSION[$this->name] = hash_hmac('sha512', uniqid(), $this->secret);
        
            return $_SESSION[$this->name];
        }
        
        return false;
    }
}
