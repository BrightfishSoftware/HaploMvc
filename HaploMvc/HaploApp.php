<?php
namespace HaploMvc;

use HaploMvc\Pattern\HaploSingleton;
use HaploMvc\Db\HaploActiveRecord;
use HaploMvc\Exception\HaploClassNotFoundException;
use ReflectionClass;

/**
 * Class HaploApp
 * @package HaploMvc
 */
class HaploApp extends HaploSingleton
{
    /** @var string */
    public $appBase;
    /** @var \HaploMvc\Config\HaploConfig */
    public $config = null;
    /** @var \HaploMvc\HaploRouter */
    public $router = null;
    /** @var \HaploMvc\Translation\HaploTranslations */
    public $translations = null;
    /** @var \HaploMvc\Cache\HaploCache */
    public $cache = null;
    /** @var \HaploMvc\Security\HaploNonce */
    public $nonce = null;
    /** @var \HaploMvc\Template\HaploTemplateFactory */
    public $template = null;
    /** @var \HaploMvc\Db\HaploDb */
    public $db = null;
    /** @var \HaploMvc\Db\HaploSqlBuilder */
    public $sqlBuilder = null;

    /**
     * Static helper method used to ensure only one instance of the class is instantiated
     *
     * @param string $appBase
     * @param bool $doInit
     * @return HaploApp
     */
    static public function getInstance($appBase = null, $doInit = true)
    {
        $class = get_called_class();
        if (!isset(static::$instances[$class]) && !is_null($appBase)) {
            static::$instances[$class] = new $class($appBase, $doInit);
        }
        return static::$instances[$class];
    }

    /**
     * @param string $appBase
     * @param bool $doInit
     */
    protected function __construct($appBase, $doInit)
    {
        $this->appBase = $appBase;
        if ($doInit) {
            $this->init();
        }
    }

    /**
     * @param string $name
     * @param string $class
     * @param array $args
     * @return bool
     */
    public function loadClass($name, $class, array $args = array())
    {
        if (isset($this->$name) && !is_null($this->$name)) {
            return false;
        }
        $this->$name = $this->getClass($class, $args);
        return true;
    }

    /**
     * @param string $class
     * @param array $args
     * @throws HaploClassNotFoundException
     * @return mixed
     */
    public function getClass($class, array $args = array())
    {
        if (!class_exists($class)) {
            throw new HaploClassNotFoundException(sprintf("Class %s not found.", $class));
        }
        if (method_exists($class, 'getInstance')) {
            return !empty($args) ? call_user_func_array("$class::getInstance", $args) : $class::getInstance($this);
        } else {
            if (!empty($args)) {
                $reflection = new ReflectionClass($class);
                return $reflection->newInstanceArgs($args);
            } else {
                return new $class($this);
            }
        }
    }

    public function init()
    {
        $this->loadClass('config', '\HaploMvc\Config\HaploConfig');
        $this->loadClass('router', '\HaploMvc\HaploRouter');
        $this->loadClass('translations', '\HaploMvc\Translation\HaploTranslations');
        $this->loadClass('cache', '\HaploMvc\Cache\HaploCache');
        $this->loadClass('nonce', '\HaploMvc\Security\HaploNonce');
        $this->loadClass('template', '\HaploMvc\Template\HaploTemplateFactory');
        if (is_null($this->db)) {
            $dbConfig = $this->config->getSection('db');
            $dbDriver = isset($dbConfig['driver']) ? $dbConfig['driver'] : 'MySql';
            $class = sprintf('\HaploMvc\Db\Haplo%sDbDriver', $dbDriver);
            $this->loadClass('db', '\HaploMvc\Db\HaploDb', array('driver' => new $class($dbConfig)));
        }
        $this->loadClass('sqlBuilder', '\HaploMvc\Db\HaploSqlBuilder', array('db' => $this->db));
        HaploActiveRecord::setDependencies($this->db, $this->sqlBuilder);
    }

    public function run()
    {
        $this->router->getAction();
    }
}
