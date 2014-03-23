<?php
namespace HaploMvc;

use HaploMvc\Pattern\HaploSingleton;
use HaploMvc\Db\HaploActiveRecord;
use HaploMvc\Config\HaploConfig;
use HaploMvc\Template\HaploTemplateFactory;
use HaploMvc\Translation\HaploTranslations;
use HaploMvc\Cache\HaploCache;
use HaploMvc\Security\HaploNonce;
use HaploMvc\Db\HaploDb;
use HaploMvc\Db\HaploSqlBuilder;

/**
 * Class HaploApp
 * @package HaploMvc
 */
class HaploApp extends HaploSingleton
{
    /** @var string */
    public $appBase;
    /** @var \HaploMvc\HaploContainer */
    public $container = null;
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
    static public function getInstance($appBase = null)
    {
        $class = get_called_class();
        if (!isset(static::$instances[$class]) && !is_null($appBase)) {
            static::$instances[$class] = new $class($appBase);
        }
        return static::$instances[$class];
    }

    /**
     * @param string $appBase
     */
    protected function __construct($appBase)
    {
        $this->appBase = $appBase;
        $this->init();
    }

    public function init()
    {
        $this->container = HaploContainer::getInstance();
        $this->container->setParam('app', $this);
        $this->container->registerService('config', function($c) {
            return HaploConfig::getInstance($c->getParam('app'));
        });
        $this->container->registerService('router', function($c) {
            return HaploRouter::getInstance($c->getParam('app'));
        });
        $this->container->registerService('translations', function($c) {
            return HaploTranslations::getInstance($c->getParam('app'));
        });
        $this->container->registerService('cache', function($c) {
            return HaploCache::getInstance($c->getParam('app'));
        });
        $this->container->registerService('nonce', function($c) {
            return HaploNonce::getInstance($c->getParam('app'));
        });
        $this->container->registerService('template', function($c) {
            return HaploTemplateFactory::getInstance($c->getParam('app'));
        });
        $this->container->registerService('db', function($c) {
            $dbConfig = $c->getParam('app')->config->getSection('db');
            $dbDriver = array_key_exists('driver', $dbConfig) ? $dbConfig['driver'] : 'MySql';
            $class = sprintf('\HaploMvc\Db\Haplo%sDbDriver', $dbDriver);
            return HaploDb::getInstance(new $class($dbConfig));
        });
        $this->container->registerService('sqlBuilder', function($c) {
            return new HaploSqlBuilder($c->getParam('app')->db);
        });
        // shortcuts
        $this->config = $this->container->getService('config');
        $this->router = $this->container->getService('router');
        $this->translations = $this->container->getService('translations');
        $this->cache = $this->container->getService('cache');
        $this->nonce = $this->container->getService('nonce');
        $this->template = $this->container->getService('template');
        $this->db = $this->container->getService('db');
        $this->sqlBuilder = $this->container->getService('sqlBuilder');
        HaploActiveRecord::setDependencies($this->db, $this->sqlBuilder);
    }

    public function run()
    {
        $this->router->getAction();
    }
}
