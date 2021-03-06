<?php
namespace HaploMvc\Action;

use HaploMvc\HaploApp;
use Actions\PageNotFound;
use HaploMvc\Exception\HaploMethodNotFoundException;
use HaploMvc\Exception\HaploActionNotFoundException;

/**
 * Class HaploAction
 * @package HaploMvc
 *
 * @method bool doInit()
 * @method bool doGet()
 * @method bool doPost()
 * @method bool doHead()
 * @method bool doPut()
 * @method bool doDelete()
 * @method bool doGetValidate
 * @method bool doGetSuccess()
 * @method bool doGetFailure()
 * @method bool doPostValidate()
 * @method bool doPostSuccess()
 * @method bool doPostFailure()
 * @method bool doAllExceptGet()
 * @method bool doAllExceptPost()
 * @method bool doAllExceptHead()
 * @method bool doAllExceptPut()
 * @method bool doAllExceptDelete()
 * @method bool doAll()
 */
abstract class HaploAction
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
     * @param \HaploMvc\HaploApp $app
     * @return \HaploMvc\Action\HaploAction
     */
    public function __construct(HaploApp $app)
    {
        $this->app = $app;
        $requestMethod = $this->app->router->getRequestMethod();

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
     * Check for valid method calls
     *
     * @param string $name
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
            new PageNotFound($this->app);
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
