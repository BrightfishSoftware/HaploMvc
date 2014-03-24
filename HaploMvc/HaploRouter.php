<?php
namespace HaploMvc;

use Closure;
use HaploMvc\Exception\HaploActionTypeNotSupportedException;
use HaploMvc\Exception\HaploNoDefault404DefinedException;
use HaploMvc\Exception\HaploNoActionDefinedException;
use HaploMvc\Exception\HaploNoRedirectUrlDefinedException;
use HaploMvc\Exception\HaploClassNotFoundException;

/**
 * Class HaploRouter
 * @package HaploMvc
 */
class HaploRouter
{
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
     * @var array
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
    public function __construct(HaploApp $app)
    {
        $this->app = $app;
    }

    /**
     * @param $routes
     */
    public function addAoutes(array $routes)
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
     * Returns the selected action based on URL pattern matched against URL
     *
     * @return string|bool The name of the selected action
     **/
    public function getAction()
    {
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
    public static function getBrowserLocale($default = 'en-us')
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
    public static function getRequestUri()
    {
        return array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : '';
    }
    
    /**
     * Get IP address of client, takes into account proxies
     *
     * @return string
     **/
    public static function getRemoteAddr()
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
    public static function getReferer()
    {
        return array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '';
    }
    
    /**
     * Helper method to get the HTTP request method used
     *
     * @return string
     **/
    public static function getRequestMethod()
    {
        return array_key_exists('REQUEST_METHOD', $_SERVER) ? strtolower($_SERVER['REQUEST_METHOD']) : '';
    }
    
    /**
     * Check if the current request is made via AJAX
     *
     * @return boolean
     **/
    public static function isAjax()
    {
        return (
            array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) &&
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
    protected function process($urls)
    {
        // by default assume no match
        $match = false;
        
        // loop through list of URL regex patterns to find a match - 
        // processing stops as soon as a match is found so URL patterns 
        // should be ordered with the most important first
        foreach ($urls as $regEx => $dest) {
            // does the current URL match - if so capture matched sub-groups
            if (preg_match("#^$regEx(\?.*)?$#", static::getRequestUri(), $this->requestVars)) {
                // the first match is the full URL - we not really 
                // interested in that so drop
                array_shift($this->requestVars);
                
                // simple form
                if (!is_array($dest)) {
                    $this->runAction($dest);
                    $match = true;
                    break;
                }

                // complex form (action or redirect)
                if (!empty($dest['type'])) {
                    switch ($dest['type']) {
                        // sets name of action in class property
                        case 'action':
                            if (!empty($dest['action'])) {
                                $this->runAction($dest['action']);
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
                $this->runAction('PageNotFound');
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
    protected function runAction($action)
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
                $action::getInstance($this->app);
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
    protected function redirect($url, $code = 302)
    {
        // map request variables to URL if applicable
        foreach ($this->requestVars as $key => $value) {
            $url = str_replace("<$key>", $value, $url);
        }
        
        header("Location: $url", true, $code);
        exit;
    }
}
