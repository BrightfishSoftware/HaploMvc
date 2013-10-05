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
    protected static $appBase;
    /** @var array */
    protected static $searchPaths = array(
        '/Includes/',
        '/HaploMvc/'
    );

    /**
     * @param string $filename
     * @throws HaploClassFileNotFoundException
     */
    public static function load_file($filename) {
        $fullPath = '';

        foreach (self::$searchPaths as $path) {
            $fullPath = static::$appBase.$path.$filename;
            if (file_exists($fullPath)) {
                break;
            }
        }

        if ($fullPath !== '') {
            require_once $fullPath;
        } else {
            throw new HaploClassFileNotFoundException(sprintf(
                '%s not found in any of the search paths.',
                $filename
            ));
        }
    }

    public static function register($appBase) {
        static::$appBase = $appBase;
        spl_autoload_register('\HaploMvc\HaploLoader::load_class');
    }

    /**
     * @param string $className
     */
    public static function load_class($className) {
        require static::$appBase.'/'.preg_replace('#\\\|_(?!.*\\\)#','/',$className).'.php';
    }
}

HaploLoader::register(APP_BASE);