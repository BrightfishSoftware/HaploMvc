<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploCacheFactory
 **/

namespace HaploMvc\Cache;

use HaploMvc\Pattern\HaploSingleton,
    HaploMvc\HaploApp,
    HaploMvc\Exception\HaploLibraryNotFoundException;

/**
 * Class HaploCache
 * @package HaploMvc
 */
class HaploCache extends HaploSingleton {
    const CACHE_TYPE_FILE = 'file';
    const CACHE_TYPE_MEMCACHED = 'memcached';
    /** @var HaploApp */
    protected $app;

    /**
     * @param HaploApp $app
     * @return mixed
     */
    public static function get_instance(HaploApp $app = null) {
        $class = get_called_class();

        if (!isset(self::$instances[$class]) && !is_null($app)) {
            self::$instances[$class] = new $class($app);
        }
        return self::$instances[$class];
    }

    /**
     * @param HaploApp $app
     */
    protected function __construct(HaploApp $app) {
        $this->app = $app;
    }

    /**
     * Factory method for getting a cache object of different types (file, memcached)
     *
     * @param string $key Key to store data against
     * @param array $options
     * @throws HaploLibraryNotFoundException
     * @return object
     */
    public function create($key, array $options = array()) {
        $defaultOptions = array(
            'type' => static::CACHE_TYPE_FILE,
            'cacheTime' => 300,
            'servers' => array(
                '127.0.0.1:11211'
            ),
            'appBase' => $this->app->appBase
        );
        $options = array_merge($defaultOptions, $options);
        $className = '\HaploMvc\Cache\Haplo'.ucfirst($options['type']).'Cache';
        
        if (class_exists($className)) {
            return new $className($key, $options);
        } else {
            throw new HaploLibraryNotFoundException("Cache library ($className) not found.");
        }
    }
}