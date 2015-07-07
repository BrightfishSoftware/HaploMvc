<?php
namespace HaploMvc\Action;

use HaploMvc\App;
use HaploMvc\Exception\MethodNotFoundException;
use HaploMvc\Exception\ActionNotFoundException;

/**
 * Class Action
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
abstract class Action
{
    /** @var App */
    protected $app;

    /**
     * Stores details of the order in which methods are run
     * Override to change order
     *
     * @var array
     **/
    protected $methodOrder = [
        'doGet',
        'doPost',
        'doHead',
        'doPut',
        'doDelete',
        'doAll'
    ];
    
    /**
     * Stores details of validation errors
     *
     * @var array
     **/
    protected $errors = [];
    
    /**
     * Stores success status of form/action
     *
     * @var boolean
     **/
    protected $success = false;

    /**
     * @param \HaploMvc\App $app
     * @return \HaploMvc\Action\Action
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $requestMethod = $this->app->router->getRequestMethod();

        if (!method_exists($this, 'doInit') || $this->doInit()) {
            foreach ($this->methodOrder as $methodType) {
                $methodName = $methodType;

                if (in_array($methodType, ['doGet', 'doPost', 'doHead', 'doPut', 'doDelete'])) {
                    if ('do'.ucfirst($requestMethod) == $methodType) {
                        $this->$methodName();

                        if (in_array($requestMethod, ['get', 'post'])) {
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
     * @throws MethodNotFoundException
     */
    public function __call($name, $args)
    {
        if (!in_array($name, [
            'doInit', 'doGet', 'doPost', 'doHead', 'doPut', 'doDelete',
            'doAllExceptGet', 'doAllExceptPost', 'doAllExceptHead',
            'doAllExceptPut', 'doAllExceptDelete', 'doAll', 'doGetValidate',
            'doGetSuccess', 'doGetFailure', 'doPostValidate',
            'doPostSuccess', 'doPostFailure'
        ])) {
            throw new MethodNotFoundException(sprintf(
                'Method %s not defined in %s.',
                $name,
                get_called_class()
            ));
        }
    }

    /**
     * Return a 404 header and page
     *
     * @throws ActionNotFoundException
     */
    public function do404()
    {
        new PageNotFound($this->app);
        exit;
    }
    
    /**
     * Redirect to another URL
     *
     * @param string $url URL to redirect to
     * @param integer $code HTTP code to send
     **/
    public function doRedirect($url, $code = 302)
    {
        header("Location: $url", true, $code);
        exit;
    }
    
    /**
     * Permanently redirect to another URL
     *
     * @param string $url URL to redirect to
     **/
    public function do301($url)
    {
        $this->doRedirect($url, 301);
    }
    
    /**
     * Temporarily redirect to another URL
     *
     * @param string $url URL to redirect to
     **/
    public function do302($url)
    {
        $this->doRedirect($url, 302);
    }
}
