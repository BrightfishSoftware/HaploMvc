<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploApp
 **/

namespace HaploMvc;

use \HaploMvc\Pattern\HaploSingleton,
    \HaploMvc\Db\HaploActiveRecord,
    \HaploMvc\Exception\HaploClassNotFoundException,
    \ReflectionClass;

/**
 * Class HaploApp
 * @package HaploMvc
 */
class HaploApp extends HaploSingleton {
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
     * @return HaploApp
     */
    static public function get_instance($appBase = null) {
        $class = get_called_class();

        if (!isset(static::$instances[$class]) && !is_null($appBase)) {
            static::$instances[$class] = new $class($appBase);
        }
        return static::$instances[$class];
    }

    /**
     * @param string $appBase
     */
    protected function __construct($appBase) {
        $this->appBase = $appBase;
    }

    /**
     * @param string $name
     * @param string $class
     * @param array $args
     * @return bool
     */
    public function load_class($name, $class, array $args = array()) {
        if (isset($this->$name) && !is_null($this->$name)) {
            return false;
        }
        $this->$name = $this->get_class($class, $args);
        return true;
    }

    /**
     * @param string $class
     * @param array $args
     * @throws HaploClassNotFoundException
     * @return mixed
     */
    public function get_class($class, array $args = array()) {
        if (!class_exists($class)) {
            throw new HaploClassNotFoundException(sprintf("Class %s not found.", $class));
        }
        if (method_exists($class, 'get_instance')) {
            return !empty($args) ? call_user_func_array("$class::get_instance", $args) : $class::get_instance($this);
        } else {
            if (!empty($args)) {
                $reflection = new ReflectionClass($class);
                return $reflection->newInstanceArgs($args);
            } else {
                return new $class($this);
            }
        }
    }

    public function init() {
        $this->load_class('config', '\HaploMvc\Config\HaploConfig');
        $this->load_class('router', '\HaploMvc\HaploRouter');
        $this->load_class('translations', '\HaploMvc\Translation\HaploTranslations');
        $this->load_class('cache', '\HaploMvc\Cache\HaploCache');
        $this->load_class('nonce', '\HaploMvc\Security\HaploNonce');
        $this->load_class('template', '\HaploMvc\Template\HaploTemplateFactory');
        if (is_null($this->db)) {
            $dbConfig = $this->config->get_section('db');
            $class = sprintf(
                '\HaploMvc\Db\Haplo%sDbDriver',
                isset($dbConfig['driver']) ? $dbConfig['driver'] : 'MySql'
            );
            $this->load_class('db', '\HaploMvc\Db\HaploDb', array(
                'driver' => new $class($dbConfig)
            ));
        }
        $this->load_class('sqlBuilder', '\HaploMvc\Db\HaploSqlBuilder', array(
            'db' => $this->db
        ));
        HaploActiveRecord::set_dependencies($this->db, $this->sqlBuilder);
    }

    public function run() {
        $this->router->get_action();
    }
}