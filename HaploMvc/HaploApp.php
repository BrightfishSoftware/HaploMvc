<?php
namespace HaploMvc;

use HaploMvc\Db\HaploActiveRecord;
use HaploMvc\Config\HaploConfig;
use HaploMvc\Template\HaploTemplateFactory;
use HaploMvc\Translation\HaploTranslator;
use HaploMvc\Cache\HaploCache;
use HaploMvc\Security\HaploNonce;
use HaploMvc\Db\HaploDb;
use HaploMvc\Db\HaploSqlBuilder;
use HaploMvc\Debug\HaploLog;

/**
 * Class HaploApp
 * @package HaploMvc
 */
class HaploApp
{
    /** @var string */
    public $appBase;
    /** @var \HaploMvc\HaploContainer */
    public $container = null;
    /** @var \HaploMvc\Config\HaploConfig  */
    public $config = null;
    /** @var \HaploMvc\HaploSession */
    public $session = null;
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
    /** @var \HaploMvc\Debug\HaploLog */
    public $log;
    /** @var \HaploMvc\Db\HaploDb */
    public $db = null;
    /** @var array */
    public $defaultServices = array(
        'config',
        'session',
        'router',
        'translator',
        'cache',
        'nonce',
        'template',
        'log',
        'db',
        'sqlBuilder'
    );

    /**
     * @param string $appBase
     * @param HaploContainer $container
     */
    public function __construct($appBase = null, HaploContainer $container = null)
    {
        $this->appBase = $appBase;
        $this->container = is_null($container) ? new HaploContainer : $container;
        $this->container->setParam('app', $this);
        $this->initServices();
    }

    protected function initServices()
    {
        $this->container->register('config', function(HaploContainer $c) {
            return new HaploConfig($c->getParam('app'));
        });
        $this->container->register('session', function(HaploContainer $c) {
            return new HaploSession($c->getParam('app'));
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
        $this->container->register('log', function(HaploContainer $c) {
            return new HaploLog($c->getParam('app'));
        });
        $this->container->register('db', function(HaploContainer $c) {
            $dbConfig = $c->getParam('app')->config->getSection('db');
            $dbDriver = array_key_exists('driver', $dbConfig) ? $dbConfig['driver'] : 'MySql';
            $class = sprintf('\HaploMvc\Db\Haplo%sDbDriver', $dbDriver);
            return new HaploDb($c->getParam('app'), new $class($dbConfig));
        });
        // shortcuts
        foreach ($this->defaultServices as $service) {
            $this->$service = $this->container->getSingle($service);
        }
    }

    public function run()
    {
        $this->router->getAction();
    }
}
