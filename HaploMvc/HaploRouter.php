<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploRouter
 **/

namespace HaploMvc;

use \Closure,
    \HaploMvc\Pattern\HaploSingleton,
    \HaploMvc\Exception\HaploActionTypeNotSupportedException,
    \HaploMvc\Exception\HaploNoDefault404DefinedException,
    \HaploMvc\Exception\HaploNoActionDefinedException,
    \HaploMvc\Exception\HaploNoRedirectUrlDefinedException,
    \HaploMvc\Exception\HaploClassNotFoundException;

/**
 * Class HaploRouter
 * @package HaploMvc
 */
class HaploRouter extends HaploSingleton {
    /** @var HaploApp */
    protected $app;
    /**
     * Stores URL mappings passed to the class on instantiation
     *
     * @var string
     **/
    protected $urls = array();
    
    /**
     * Contains values of matched variables in the selected URL pattern
     *
     * @var string
     **/
    protected $requestVars = array();
    
    /**
     * Raw action name (as specified in URL mapping)
     *
     * @var string
     **/
    protected $action;

    /**
     * @param HaploApp $app
     */
    protected function __construct(HaploApp $app) {
        $this->app = $app;
    }

    /**
     * Static helper method used to ensure only one instance of the class is instantiated
     * This overrides the base version in the abstract HaploSingleton class because we
     * need to support parameters
     *
     * @param HaploApp $app
     * @return HaploRouter
     */
    public static function get_instance(HaploApp $app = null) {
        $class = get_called_class();
        if (!isset(self::$instances[$class]) && !is_null($app)) {
            self::$instances[$class] = new $class($app);
        }
        return self::$instances[$class];
    }

    /**
     * @param $routes
     */
    public function add_routes(array $routes) {
        foreach ($routes as $pattern => $destination) {
            $this->add_route($pattern, $destination);
        }
    }

    /**
     * Adds a URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function add_route($pattern, $destination) {
        $this->urls[$pattern] = $destination;
    }
    
    /**
     * Adds a URL route for a specific HTTP request type
     *
     * @param string $verb - HTTP request type to add route for
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    protected function add_verb_route($verb, $pattern, $destination) {
        if (static::get_request_method() === $verb) {
            $this->add_route($pattern, $destination);
        }
    }
    
    /**
     * Adds a HTTP GET URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function add_get_route($pattern, $destination) {
        $this->add_verb_route('get', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP POST URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function add_post_route($pattern, $destination) {
        $this->add_verb_route('post', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP HEAD URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function add_head_route($pattern, $destination) {
        $this->add_verb_route('head', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP PUT URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function add_put_route($pattern, $destination) {
        $this->add_verb_route('put', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP DELETE URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function add_delete_route($pattern, $destination) {
        $this->add_verb_route('delete', $pattern, $destination);
    }
    
    /**
     * Returns the selected action based on URL pattern matched against URL
     *
     * @return string|bool The name of the selected action
     **/
    public function get_action() {
        $this->process($this->urls);
    }
    
    /**
     * Provides a way to accessing matched URL sub-patterns, optionally set a default 
     * value if the specified sub-pattern wasn't found
     *
     * @param string $name Name of parameter to get value for
     * @param mixed $default Default value to use if the parameter hasn't been set
     * @return mixed|null
     */
    public function get_request_var($name, $default = null) {
        return isset($this->requestVars[$name]) ? $this->requestVars[$name] : $default;
    }
    
    /**
     * Get user's default locale from their browser
     *
     * @param string $default default locale to use
     * @return string locale - if not set use a default
     **/
    public static function get_browser_locale($default = 'en-us') {
        if (
            !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && 
            preg_match('/^[a-z-]+$/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'])
        ) {
            return strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
        return $default;
    }
    
    /**
     * Get request URI
     *
     * @return string
     **/
    public static function get_request_uri() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }
    
    /**
     * Get IP address of client, takes into account proxies
     *
     * @return string
     **/
    public static function get_remote_addr() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return trim($ip);
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * Get referring page
     *
     * @return string
     **/
    public static function get_referer() {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }
    
    /**
     * Helper method to get the HTTP request method used
     *
     * @return string
     **/
    public static function get_request_method() {
        return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : '';
    }
    
    /**
     * Check if the current request is made via AJAX
     *
     * @return boolean
     **/
    public static function is_ajax() {
        return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        );
    }

    /**
     * Logic to work out which view should be loaded and process parameters
     *
     * @param array $urls URL patterns to process current URL against
     * @throws HaploActionTypeNotSupportedException
     * @throws HaploNoDefault404DefinedException
     * @throws HaploNoActionDefinedException
     * @throws HaploNoRedirectUrlDefinedException
     * @return bool
     */
    protected function process($urls) {
        // by default assume no match
        $match = false;
        
        // loop through list of URL regex patterns to find a match - 
        // processing stops as soon as a match is found so URL patterns 
        // should be ordered with the most important first
        foreach ($urls as $regEx => $dest) {
            // does the current URL match - if so capture matched sub-groups
            if (preg_match("#^$regEx(\?.*)?$#", static::get_request_uri(), $this->requestVars)) {
                // the first match is the full URL - we not really 
                // interested in that so drop
                array_shift($this->requestVars);
                
                // simple form
                if (!is_array($dest)) {
                    $this->run_action($dest);
                    $match = true;
                    break;
                }

                // complex form (action or redirect)
                if (!empty($dest['type'])) {
                    switch ($dest['type']) {
                        // sets name of action in class property
                        case 'action':
                            if (!empty($dest['action'])) {
                                $this->run_action($dest['action']);
                            } else {
                                throw new HaploNoActionDefinedException("No action defined for $regEx.");
                            }
                            break;
                        // performs http redirect (301, 302 etc)
                        case 'redirect':
                            if (!empty($dest['url'])) {
                                !empty($dest['code']) ? $this->redirect($dest['url'], $dest['code']) : $this->redirect($dest['url']);
                            } else {
                                throw new HaploNoRedirectUrlDefinedException("No redirect URL defined for $regEx.");
                            }
                            break;
                    }

                    $match = true;
                    break;
                } else {
                    throw new HaploNoActionDefinedException(
                        "No action type defined for $regEx. Should be one of 'action', 'redirect' or 'sub-patterns'."
                    );
                }
            }
        }
        
        // if none of the URL patterns matches look for a default 404 action
        if (!$match) {
            // send http 404 error header
            header('HTTP/1.1 404 Not Found');
            
            // check for existence of a 404 action
            if (class_exists('\\Actions\\PageNotFound')) {
                $this->run_action('PageNotFound');
            } else {
                throw new HaploNoDefault404DefinedException('No default 404 action found.');
            }
        }
        
        return $match;
    }

    /**
     *  Simply sets the class/closure of the action
     *
     * @param string $action Name of action to set as selected
     * @throws HaploClassNotFoundException
     */
    protected function run_action($action) {
        if ($action instanceof Closure) {
            $action($this->app);
        } else {
            // map request variables to action name if applicable
            foreach ($this->requestVars as $key => $value) {
                $action = str_replace("<$key>", $value, $action);
            }
            $action = '\\Actions\\'.$action;
            if (class_exists($action)) {
                $action::get_instance($this->app);
            } else {
                throw new HaploClassNotFoundException(sprintf("%s not found", $action));
            }
        }
    }
    
    /**
     * Do http redirect (301, 302 etc)
     *
     * @param string $url URL to redirect to
     * @param integer $code HTTP code to send - probably 301 (permanently modified) or 302 (temporarily moved)
     **/
    protected function redirect($url, $code = 302) {
        // map request variables to URL if applicable
        foreach ($this->requestVars as $key => $value) {
            $url = str_replace("<$key>", $value, $url);
        }
        
        header("Location: $url", true, $code);
        exit;
    }
}
