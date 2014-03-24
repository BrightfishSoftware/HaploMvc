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
 */
class HaploApp extends HaploSingleton
{
    /** @var string */
    public $appBase;
    /** @var \HaploMvc\HaploContainer */
    public $container = null;
    /** @var \HaploMvc\Config\HaploConfig  */
    public $config = null;
    /** @var \HaploMvc\HaploRouter */
    public $router = null;
    /** @var \HaploMvc\Translation\HaploTranslator */
    public $translator = null;
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
        $this->container->register('config', function(HaploContainer $c) {
            return new HaploConfig($c->getParam('app'));
        });
        $this->container->register('router', function(HaploContainer $c) {
            return new HaploRouter($c->getParam('app'));
        });
        $this->container->register('translator', function(HaploContainer $c) {
            return new HaploTranslator($c->getParam('app'));
        });
        $this->container->register('cache', function(HaploContainer $c) {
            return new HaploCache($c->getParam('app'));
        });
        $this->container->register('nonce', function(HaploContainer $c) {
            return new HaploNonce($c->getParam('app'));
        });
        $this->container->register('template', function(HaploContainer $c) {
            return new HaploTemplateFactory($c->getParam('app'));
        });
        $this->container->register('db', function(HaploContainer $c) {
            $dbConfig = $c->getParam('app')->config->getSection('db');
            $dbDriver = array_key_exists('driver', $dbConfig) ? $dbConfig['driver'] : 'MySql';
            $class = sprintf('\HaploMvc\Db\Haplo%sDbDriver', $dbDriver);
            return new HaploDb(new $class($dbConfig));
        });
        $this->container->register('sqlBuilder', function(HaploContainer $c) {
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
