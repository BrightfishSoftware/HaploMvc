<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploApp
 **/

namespace HaploMvc;

use \HaploMvc\Pattern\HaploSingleton,
    \HaploMvc\Template\HaploTemplateFactory,
    \HaploMvc\Config\HaploConfig,
    \HaploMvc\Cache\HaploCacheFactory,
    \HaploMvc\Security\HaploNonce,
    \HaploMvc\Translation\HaploTranslations;

/**
 * Class HaploApp
 * @package HaploMvc
 */
class HaploApp extends HaploSingleton {
    /** @var string */
    public $appBase;
    /** @var HaploConfig */
    public $config;
    /** @var HaploRouter */
    public $router;
    /** @var HaploConfig */
    public $translations;
    /** @var HaploCacheFactory */
    public $cache;
    /** @var HaploNonce */
    public $nonce;
    /** @var HaploTemplateFactory */
    public $template;

    /**
     * Static helper method used to ensure only one instance of the class is instantiated
     *
     * @param string $appBase
     * @return HaploApp
     */
    static public function get_instance($appBase = null) {
        $class = get_called_class();

        if (!isset(self::$instances[$class]) && !is_null($appBase)) {
            self::$instances[$class] = new $class($appBase);
        }
        return self::$instances[$class];
    }

    /**
     * @param string $appBase
     */
    protected function __construct($appBase) {
        $this->appBase = $appBase;
        $this->config = HaploConfig::get_instance($this);
        $this->router = HaploRouter::get_instance($this);
        $this->translations = HaploTranslations::get_instance($this);
        $this->cache = HaploCacheFactory::get_instance($this);
        $this->nonce = HaploNonce::get_instance($this);
        $this->template = HaploTemplateFactory::get_instance($this);
    }

    public function run() {
        $this->router->get_action();
    }
}