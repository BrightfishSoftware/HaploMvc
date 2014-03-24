<?php
namespace HaploMvc\Cache;

use HaploMvc\HaploApp;
use HaploMvc\Exception\HaploLibraryNotFoundException;

/**
 * Class HaploCache
 * @package HaploMvc
 */
class HaploCache
{
    const CACHE_TYPE_FILE = 'file';
    const CACHE_TYPE_MEMCACHED = 'memcached';
    /** @var HaploApp */
    protected $app;

    /**
     * @param HaploApp $app
     */
    public function __construct(HaploApp $app)
    {
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
    public function create($key, array $options = array())
    {
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
