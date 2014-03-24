<?php
namespace HaploMvc;

use Closure;
use HaploMvc\Pattern\HaploSingleton;

/**
 * Class HaploContainer
 * @package HaploMvc
 */
class HaploContainer extends HaploSingleton
{
    /** @var array */
    protected $items = array();
    /** @var array */
    protected $params = array();

    protected function __construct()
    {

    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new $class;
        }
        return static::$instances[$class];
    }

    /**
     * @param string $name
     * @param Closure $callback
     */
    public function registerService($name, $callback)
    {
        $this->items[$name] = $callback;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getService($name)
    {
        return array_key_exists($name, $this->items) ? $this->items[$name]($this) : false;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return array_key_exists($name, $this->params) ? $this->params[$name] : $default;
    }
}
