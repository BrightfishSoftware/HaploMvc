<?php
namespace HaploMvc;

use HaploMvc\Config\Config;
use HaploMvc\Db\ActiveRecord;
use HaploMvc\Template\TemplateFactory;
use HaploMvc\Translation\Translator;
use HaploMvc\Cache\Cache;
use HaploMvc\Security\Nonce;
use HaploMvc\Db\Db;
use HaploMvc\Debug\Log;
use HaploMvc\Exception\ServiceNotFoundException;

/**
 * Class App
 * @package HaploMvc
 * @property \HaploMvc\Config\Config $config
 * @property \HaploMvc\Session $session
 * @property \HaploMvc\Router $router
 * @property \HaploMvc\Translation\Translator $translator
 * @property \HaploMvc\Cache\Cache $cache
 * @property \HaploMvc\Security\Nonce $nonce
 * @property \HaploMvc\Template\TemplateFactory $template
 * @property \HaploMvc\Debug\Log $log
 * @property \HaploMvc\Db\Db $db
 */
class App
{
    /** @var string */
    protected $timezone = 'UTC';
    /** @var string */
    public $appBase;
    /** @var \HaploMvc\Container */
    public $container = null;

    /**
     * @param string $appBase
     * @param Container $container
     */
    public function __construct($appBase = null, Container $container = null)
    {
        $this->appBase = $appBase;
        $this->container = is_null($container) ? new Container : $container;
        $this->container->setParam('app', $this);
        $this->init();
    }

    protected function init()
    {
        $this->container->register('config', function(Container $c) {
            return new Config($c->getParam('app'));
        });
        $this->container->register('session', function(Container $c) {
            return new Session($c->getParam('app'));
        });
        $this->container->register('router', function(Container $c) {
            return new Router($c->getParam('app'));
        });
        $this->container->register('translator', function(Container $c) {
            return new Translator($c->getParam('app'));
        });
        $this->container->register('cache', function(Container $c) {
            return new Cache($c->getParam('app'));
        });
        $this->container->register('nonce', function(Container $c) {
            return new Nonce($c->getParam('app'));
        });
        $this->container->register('template', function(Container $c) {
            return new TemplateFactory($c->getParam('app'));
        });
        $this->container->register('log', function(Container $c) {
            return new Log($c->getParam('app'));
        });
        $this->container->register('db', function(Container $c) {
            $dbConfig = $c->getParam('app')->config->getSection('db');
            $dbDriver = array_key_exists('driver', $dbConfig) ? $dbConfig['driver'] : 'MySql';
            $class = sprintf('\HaploMvc\Db\%sDbDriver', $dbDriver);
            $db = new Db($c->getParam('app'), new $class($dbConfig));
            return $db;
        });
        ActiveRecord::setDependencies($this->container->getParam('app'));
        date_default_timezone_set($this->timezone);
    }

    /**
     * @param string $name
     * @throws ServiceNotFoundException
     * @return mixed
     */
    public function __get($name)
    {
        if (in_array($name, ['config', 'session', 'router', 'translator', 'cache', 'nonce', 'template', 'log', 'db'])) {
            return $this->container->getSingle($name);
        } else {
            throw new ServiceNotFoundException(sprintf('Service %s not found in %s.', get_called_class(), $name));
        }
    }

    /**
     * @param string $timezone
     * @return App $this
     */
    public function set_timezone($timezone) {
        $this->timezone = $timezone;
        date_default_timezone_set($this->timezone);
        return $this;
    }

    /**
     * @return string
     */
    public function get_timezone() {
        return $this->timezone;
    }

    public function run()
    {
        $this->router->runAction($this->router->process() ? $this->router->getAction() : 'PageNotFound');
    }
}
