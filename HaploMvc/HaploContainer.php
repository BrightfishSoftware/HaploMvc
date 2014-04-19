<?php
namespace HaploMvc;

use Closure;

/**
 * Class HaploContainer
 * @package HaploMvc
 */
class HaploContainer
{
    /** @var array */
    protected $items = array();
    /** @var array */
    protected $params = array();
    /** @var array */
    protected $objects = array();

    public function __construct()
    {

    }

    /**
     * @param string $name
     * @param Closure $callback
     * @param bool $replace
     */
    public function register($name, $callback, $replace = false)
    {
        if (!array_key_exists($name, $this->items) || $replace) {
            $this->items[$name] = $callback;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->items)) {
            $this->objects[$name] = $this->items[$name]($this);
            return $this->objects[$name];
        }
        return false;
    }

    public function getSingle($name) {
        if (array_key_exists($name, $this->objects) && !is_null($this->objects[$name])) {
            return $this->objects[$name];
        }
        return $this->get($name);
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
