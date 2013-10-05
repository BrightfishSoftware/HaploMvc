<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploAction
 **/

namespace HaploMvc;

/**
 * Class HaploAction
 * @package HaploMvc
 */
abstract class HaploAction extends HaploSingleton {
    /**
     * @var HaploApp
     */
    protected $app;

    /**
     * Stores details of the order in which methods are run
     * Override to change order
     *
     * @var array
     **/
    protected $methodOrder = array(
        'do_get',
        'do_post',
        'do_head',
        'do_put',
        'do_delete',
        'do_all'
    );
    
    /**
     * Stores details of validation errors
     *
     * @var array
     **/
    protected $errors = array();
    
    /**
     * Stores success status of form/action
     *
     * @var boolean
     **/
    protected $success = false;

    /**
     * Class constructor - not called directly as the class is instantiated as a Singleton
     *
     * @param HaploApp $app
     * @return HaploAction
     */
    protected function __construct(HaploApp $app) {
        $this->app = $app;
        $requestMethod = HaploRouter::get_request_method();

        if (!method_exists($this, 'do_init') || $this->do_init()) {
            foreach ($this->methodOrder as $methodType) {
                $methodName = $methodType;

                if (in_array($methodType, array('do_get', 'do_post', 'do_head', 'do_put', 'do_delete'))) {
                    if ("do_$requestMethod" == $methodType) {
                        $this->$methodName();

                        if (in_array($requestMethod, array('get', 'post'))) {
                            $methodName = 'do_'.$requestMethod.'_validate';
                            $methodName = 'do_'.$requestMethod.($this->$methodName() ? '_success' : '_failure');
                            $this->$methodName();
                        }
                    } else {
                        $methodName = str_replace('do_', 'do_all_except_', $methodName);
                        $this->$methodName();
                    }
                } else {
                    $this->$methodName();
                }
            }
        }
    }

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
     * Check for valid method calls
     *
     * @param string $name Name of method being run
     * @param array $args
     * @throws HaploMethodNotFoundException
     */
    public function __call($name, $args) {
        if (!in_array($name, array(
            'do_init', 'do_get', 'do_post', 'do_head', 'do_put', 'do_delete', 
            'do_all_except_get', 'do_all_except_post', 'do_all_except_head', 
            'do_all_except_put', 'do_all_except_delete', 'do_all', 'do_get_validate', 
            'do_get_success', 'do_get_failure', 'do_post_validate', 
            'do_post_success', 'do_post_failure'
        ))) {
            throw new HaploMethodNotFoundException(sprintf(
                'Method %s not defined in %s.',
                $name,
                get_called_class()
            ));
        }
    }

    /**
     * Return a 404 header and page
     *
     * @throws HaploActionNotFoundException
     */
    protected function do_404() {
        header('HTTP/1.1 404 Not Found');
            
        if (class_exists('\\Actions\\PageNotFound')) {
            \Actions\PageNotFound::get_instance($this->app);
            exit;
        } else {
            throw new HaploActionNotFoundException('No default 404 action found. Add \Actions\PageNotFound to suppress this message.');
        }
    }
    
    /**
     * Redirect to another URL
     *
     * @param string $url URL to redirect to
     * @param integer $code HTTP code to send
     **/
    protected function do_redirect($url, $code = 302) {
        header("Location: $url", true, $code);
        exit;
    }
    
    /**
     * Permanently redirect to another URL
     *
     * @param string $url URL to redirect to
     **/
    protected function do_301($url) {
        $this->do_redirect($url, 301);
    }
    
    /**
     * Temporarily redirect to another URL
     *
     * @param string $url URL to redirect to
     **/
    protected function do_302($url) {
        $this->do_redirect($url, 302);
    }
}