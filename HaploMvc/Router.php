<?php
namespace HaploMvc;

use Closure;
use HaploMvc\Exception\ClassNotFoundException;

/**
 * Class Router
 * @package HaploMvc
 */
class Router
{
    /** @var App */
    protected $app;
    /**
     * Stores URL mappings passed to the class on instantiation
     *
     * @var array
     **/
    protected $urls = [];
    
    /**
     * Contains values of matched variables in the selected URL pattern
     *
     * @var array
     **/
    protected $requestVars = [];
    
    /**
     * Raw action name (as specified in URL mapping)
     *
     * @var string
     **/
    protected $action;

    /** @var string */
    protected $redirectUrl;

    /** @var int */
    protected $httpCode;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param $routes
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $pattern => $destination) {
            $this->addRoute($pattern, $destination);
        }
    }

    /**
     * Adds a URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function addRoute($pattern, $destination)
    {
        $this->urls[$pattern] = $destination;
    }
    
    /**
     * Adds a URL route for a specific HTTP request type
     *
     * @param string $verb - HTTP request type to add route for
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    protected function addVerbRoute($verb, $pattern, $destination)
    {
        if (static::getRequestMethod() === $verb) {
            $this->addRoute($pattern, $destination);
        }
    }
    
    /**
     * Adds a HTTP GET URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function addGetRoute($pattern, $destination)
    {
        $this->addVerbRoute('get', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP POST URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function addPostRoute($pattern, $destination)
    {
        $this->addVerbRoute('post', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP HEAD URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function addHeadRoute($pattern, $destination)
    {
        $this->addVerbRoute('head', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP PUT URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function addPutRoute($pattern, $destination)
    {
        $this->addVerbRoute('put', $pattern, $destination);
    }
    
    /**
     * Adds a HTTP DELETE URL route
     *
     * @param string $pattern Key/value pair containing URL pattern and action to map to
     * @param array|string $destination
     */
    public function addDeleteRoute($pattern, $destination)
    {
        $this->addVerbRoute('delete', $pattern, $destination);
    }
    
    /**
     * Provides a way to accessing matched URL sub-patterns, optionally set a default 
     * value if the specified sub-pattern wasn't found
     *
     * @param string $name Name of parameter to get value for
     * @param mixed $default Default value to use if the parameter hasn't been set
     * @return mixed|null
     */
    public function getRequestVar($name, $default = null)
    {
        return array_key_exists($name, $this->requestVars) ? $this->requestVars[$name] : $default;
    }
    
    /**
     * Get user's default locale from their browser
     *
     * @param string $default default locale to use
     * @return string locale - if not set use a default
     **/
    public function getBrowserLocale($default = 'en-us')
    {
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
    public function getRequestUri()
    {
        return array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '';
    }
    
    /**
     * Get IP address of client, takes into account proxies
     *
     * @return string
     **/
    public function getRemoteAddr()
    {
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
    public function getReferer()
    {
        return array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '';
    }
    
    /**
     * Helper method to get the HTTP request method used
     *
     * @return string
     **/
    public function getRequestMethod()
    {
        return array_key_exists('REQUEST_METHOD', $_SERVER) ? strtolower($_SERVER['REQUEST_METHOD']) : '';
    }
    
    /**
     * Check if the current request is made via AJAX
     *
     * @return boolean
     **/
    public function isAjax()
    {
        return (
            array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        );
    }

    /**
     * Process rules and set action
     *
     * @return bool
     */
    public function process()
    {
        // loop through list of URL regex patterns to find a match - 
        // processing stops as soon as a match is found so URL patterns 
        // should be ordered with the most important first
        foreach ($this->urls as $regEx => $action) {
            if (preg_match("#^$regEx(\\?.*)?$#", static::getRequestUri(), $this->requestVars)) {
                // the first match is the full URL - we not really interested in that so drop
                array_shift($this->requestVars);
                $this->action = $action;
                return true;
            }
        }

        $this->action = '';
        return false;
    }

    /**
     *  Simply runs the class/closure of the action
     *
     * @param string $action
     * @throws ClassNotFoundException
     */
    public function runAction($action)
    {
        if ($action instanceof Closure) {
            $action($this->app);
        } else {
            // map request variables to action name if applicable
            foreach ($this->requestVars as $key => $value) {
                $action = str_replace("<$key>", $value, $action);
            }
            $action = '\\Actions\\'.$action;
            if (class_exists($action)) {
                new $action($this->app);
            } else {
                throw new ClassNotFoundException(sprintf("%s not found", $action));
            }
        }
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getRedirectUrl() {
        return $this->redirectUrl;
    }

    /**
     * @return int
     */
    public function getHttpCode() {
        return $this->httpCode;
    }
}
