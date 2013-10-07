<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploLoader
 **/
namespace HaploMvc;

/**
 * Class HaploLoader
 * @package HaploMvc
 */
class HaploLoader {
    /** @var string */
    protected static $appBase;
    /** @var array */
    protected static $namespaces = array(
        'Actions',
        'HaploMvc',
        'Includes',
        'Models',
        'Zend'
    );

    /**
     * @param $appBase
     */
    public static function register($appBase) {
        static::$appBase = $appBase;
        spl_autoload_register('\HaploMvc\HaploLoader::load_class');
    }

    /**
     * @param string $className
     * @return bool
     */
    public static function load_class($className) {
        foreach (static::$namespaces as $namespace) {
            if (substr($className, 0, strlen($namespace)) === $namespace) {
                $filename = static::$appBase.'/'.preg_replace('#\\\|_(?!.*\\\)#','/',$className).'.php';
                if (is_readable($filename)) {
                    require $filename;
                    return true;
                }
            }
        }
        return false;
    }
}

HaploLoader::register(APP_BASE);