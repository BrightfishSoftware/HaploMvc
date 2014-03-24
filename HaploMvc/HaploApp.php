<?php
namespace HaploMvc;

use HaploMvc\Pattern\HaploSingleton;
use HaploMvc\Db\HaploActiveRecord;
use HaploMvc\Config\HaploConfig;
use HaploMvc\Template\HaploTemplateFactory;
use HaploMvc\Translation\HaploTranslator;
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
 * @property \HaploMvc\Translation\HaploTranslator $translations
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
    /** @var array */
    public $defaultServices = array(
        'config',
        'router',
        'translator',
        'cache',
        'nonce',
        'template',
        'db',
        'sqlBuilder'
    );

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

    public function initServices()
    {
        $this->container->register('config', function($c) {
            return new HaploConfig($c->getParam('app'));
        });
        $this->container->register('router', function($c) {
            return new HaploRouter($c->getParam('app'));
        });
        $this->container->register('translator', function($c) {
            return new HaploTranslator($c->getParam('app'));
        });
        $this->container->register('cache', function($c) {
            return new HaploCache($c->getParam('app'));
        });
        $this->container->register('nonce', function($c) {
            return new HaploNonce($c->getParam('app'));
        });
        $this->container->register('template', function($c) {
            return new HaploTemplateFactory($c->getParam('app'));
        });
        $this->container->register('db', function($c) {
            $dbConfig = $c->getParam('app')->config->getSection('db');
            $dbDriver = array_key_exists('driver', $dbConfig) ? $dbConfig['driver'] : 'MySql';
            $class = sprintf('\HaploMvc\Db\Haplo%sDbDriver', $dbDriver);
            return new HaploDb(new $class($dbConfig));
        });
        $this->container->register('sqlBuilder', function($c) {
            return new HaploSqlBuilder($c->getParam('app')->db);
        });
        // shortcuts
        foreach ($this->defaultServices as $service) {
            $this->$service = $this->container->getSingle($service);
        }
        HaploActiveRecord::setDependencies($this->db, $this->sqlBuilder);
    }

    public function run()
    {
        $this->router->getAction();
    }
}
