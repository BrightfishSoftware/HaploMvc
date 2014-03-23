<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploAction
 **/

namespace HaploMvc\Action;

use HaploMvc\Pattern\HaploSingleton,
    HaploMvc\HaploApp,
    HaploMvc\HaploRouter,
    HaploMvc\Exception\HaploMethodNotFoundException,
    HaploMvc\Exception\HaploActionNotFoundException;

/**
 * Class HaploAction
 * @package HaploMvc
 */
abstract class HaploAction extends HaploSingleton
{
    /** @var HaploApp */
    protected $app;

    /**
     * Stores details of the order in which methods are run
     * Override to change order
     *
     * @var array
     **/
    protected $methodOrder = array(
        'doGet',
        'doPost',
        'doHead',
        'doPut',
        'doDelete',
        'doAll'
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
     * @param \HaploMvc\HaploApp $app
     * @return \HaploMvc\Action\HaploAction
     */
    protected function __construct(HaploApp $app)
    {
        $this->app = $app;
        $requestMethod = HaploRouter::getRequestMethod();

        if (!method_exists($this, 'doInit') || $this->doInit()) {
            foreach ($this->methodOrder as $methodType) {
                $methodName = $methodType;

                if (in_array($methodType, array('doGet', 'doPost', 'doHead', 'doPut', 'doDelete'))) {
                    if ('do'.ucfirst($requestMethod) == $methodType) {
                        $this->$methodName();

                        if (in_array($requestMethod, array('get', 'post'))) {
                            $methodName = 'do'.ucfirst($requestMethod).'Validate';
                            $methodName = 'do'.ucfirst($requestMethod).($this->$methodName() ? 'Success' : 'Failure');
                            $this->$methodName();
                        }
                    } else {
                        $methodName = str_replace('do', 'doAllExcept', $methodName);
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
    public static function getInstance(HaploApp $app = null)
    {
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
    public function __call($name, $args)
    {
        if (!in_array($name, array(
            'doInit', 'doGet', 'doPost', 'doHead', 'doPut', 'doDelete',
            'doAllExceptGet', 'doAllExceptPost', 'doAllExceptHead',
            'doAllExceptPut', 'doAllExceptDelete', 'doAll', 'doGetValidate',
            'doGetSuccess', 'doGetFailure', 'doPostValidate',
            'doPostSuccess', 'doPostFailure'
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
    protected function do404()
    {
        header('HTTP/1.1 404 Not Found');
            
        if (class_exists('\Actions\PageNotFound')) {
            \Actions\PageNotFound::getInstance($this->app);
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
    protected function doRedirect($url, $code = 302)
    {
        header("Location: $url", true, $code);
        exit;
    }
    
    /**
     * Permanently redirect to another URL
     *
     * @param string $url URL to redirect to
     **/
    protected function do301($url)
    {
        $this->doRedirect($url, 301);
    }
    
    /**
     * Temporarily redirect to another URL
     *
     * @param string $url URL to redirect to
     **/
    protected function do302($url)
    {
        $this->doRedirect($url, 302);
    }
}
