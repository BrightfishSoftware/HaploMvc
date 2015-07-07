<?php
namespace HaploMvc\Cache;

use Memcached;

/**
 * Class MemcachedCache
 * @package HaploMvc
 */
class MemcachedCache
{
    /** @var \Memcached */
    protected $memcached;
    /** @var string */
    protected $key;
    /** @var int */
    protected $cacheTime;
    /** @var array */
    protected static $cache = [];

    /**
     * @param string $key
     * @param array $options
     */
    public function __construct($key, array $options)
    {
        $this->memcached = new Memcached();
        
        if (!empty($options['servers'])) {
            foreach ($options['servers'] as $server) {
                list($server, $port) = explode(':', $server);
                $this->memcached->addServer($server, $port);
            }
        } else {
            $this->memcached->addServer('127.0.0.1', 11211);
        }
        
        $this->key = md5($key);
        $this->cacheTime = $options['cacheTime'];
    }

    /**
     * @return bool
     */
    public function check()
    {
        if (array_key_exists($this->key, static::$cache)) {
            return true;
        }
        
        $value = $this->memcached->get($this->key);
        
        if ($this->memcached->getResultCode() == Memcached::RES_SUCCESS) {
            static::$cache[$this->key] = $value;
            return true;
        }
        
        return false;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        // unable to check expired cache item
        return false;
    }

    /**
     * @param mixed $contents
     * @return bool
     */
    public function set($contents)
    {
        static::$cache[$this->key] = $contents;
        return $this->memcached->set($this->key, $contents, time() + $this->cacheTime);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        if (isset(static::$cache[$this->key])) {
            return static::$cache[$this->key];
        }
        
        return $this->memcached->get($this->key);
    }

    /**
     * @return bool
     */
    public function reValidate()
    {
        // unable to re-validate expired cache item
        return false;
    }
}
