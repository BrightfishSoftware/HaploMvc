<?php
namespace HaploMvc;

use HaploMvc\Exception\HaploUndefinedException;
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
 *
 * @var \HaploMvc\Config\HaploConfig $config
 * @property \HaploMvc\HaploRouter $router
 * @property \HaploMvc\Translation\HaploTranslations $translations
 * @property \HaploMvc\Cache\HaploCache $cache
 * @property \HaploMvc\Security\HaploNonce $nonce
 * @property \HaploMvc\Template\HaploTemplateFactory $template
 * @property \HaploMvc\Db\HaploDb $db
 * @property \HaploMvc\Db\HaploSqlBuilder $sqlBuilder
 */
class HaploApp extends HaploSingleton
{
    /** @var string */
    public $appBase;
    /** @var \HaploMvc\HaploContainer */
    public $container = null;

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
        $this->container = HaploContainer::getInstance();
        $this->container->setParam('app', $this);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception\HaploUndefinedException
     */
    public function __get($name)
    {
        if ($service = $this->container->getService($name)) {
            return $service;
        } else {
            throw new HaploUndefinedException('Property not found in HaploApp or HaploContainer.');
        }
    }

    public function initServices()
    {
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
        HaploActiveRecord::setDependencies($this->db, $this->sqlBuilder);
    }

    public function run()
    {
        $this->router->getAction();
    }
}
