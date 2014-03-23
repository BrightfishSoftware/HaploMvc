<?php
namespace HaploMvc\Action;

use HaploMvc\HaploApp;
use HaploMvc\Pattern\HaploSingleton;

/**
 * Class HaploSlimAction
 * @package HaploMvc
 */
abstract class HaploSlimAction extends HaploSingleton
{
    /** @var HaploApp */
    protected $app;

    /**
     * Class constructor - not called directly as the class is instantiated as a Singleton
     *
     * @param \HaploMvc\HaploApp $app
     * @return \HaploMvc\Action\HaploSlimAction
     */
    public function __construct(HaploApp $app)
    {
        $this->app = $app;
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
}
