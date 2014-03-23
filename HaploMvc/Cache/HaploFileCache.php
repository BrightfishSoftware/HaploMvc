<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploFileCache
 **/

namespace HaploMvc\Cache;

/**
 * Class HaploFileCache
 * @package HaploMvc
 */
class HaploFileCache
{
    /** @var string */
    protected $file;
    /** @var string */
    protected $fileLock;
    /** @var int */
    protected $cacheTime;
    /** @var string */
    protected $shortKey;
    /** @var array */
    protected static $cache = array();

    /**
     * Constructor for class
     *
     * @param string $key Key for cached data to look up/set
     * @param array $options
     * @return HaploFileCache
     */
    public function __construct($key, array $options)
    {
        $this->shortKey = 'HaploCache-'.sha1($key);
        $this->file = $options['appBase']."/Cache/$this->shortKey.txt";
        $this->fileLock = "$this->file.lock";
        $this->cacheTime = $options['cacheTime'];
    }

    /**
     * Checks if cache item exists and content isn't stale
     *
     * @return bool
     **/
    public function check()
    {
        if (array_key_exists($this->shortKey, static::$cache) || file_exists($this->fileLock)) {
            return true;
        }
        return (file_exists($this->file) && ($this->cacheTime == -1 || time() - filemtime($this->file) <= $this->cacheTime));
    }

    /**
     * Checks if cache item exists but isn't concerned if content is stale
     *
     * @return bool
     **/
    public function exists()
    {
        return (array_key_exists($this->shortKey, static::$cache)) || (file_exists($this->file) || file_exists($this->fileLock));
    }

    /**
     * Cache some content
     *
     * @param mixed $contents Data to cache
     * @return bool
     **/
    public function set($contents)
    {
        if (!file_exists($this->fileLock)) {
            if (file_exists($this->file)) {
                copy($this->file, $this->fileLock);
            }
            $file = fopen($this->file, 'w');
            fwrite($file, serialize($contents));
            fclose($file);
            if (file_exists($this->fileLock)) {
                unlink($this->fileLock);
            }
            static::$cache[$this->shortKey] = $contents;
            return true;
        }     
        return false;
    }

    /**
     * Get the contents of the cache
     *
     * @return bool
     **/
    public function get()
    {
        if ($this->exists()) {
            if (array_key_exists($this->shortKey, static::$cache)) {
                return static::$cache[$this->shortKey];
            } else if (file_exists($this->fileLock)) {
                static::$cache[$this->shortKey] = unserialize(file_get_contents($this->fileLock));
                return static::$cache[$this->shortKey];
            } else {
                static::$cache[$this->shortKey] = unserialize(file_get_contents($this->file));
                return static::$cache[$this->shortKey];
            }
        }
        return false;

    }

    /**
     * Re-validates stale content in cache
     *
     **/
    public function reValidate()
    {
        touch($this->file);
    }
}
