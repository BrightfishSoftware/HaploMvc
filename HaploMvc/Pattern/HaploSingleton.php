<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploSingleton
 **/

namespace HaploMvc\Pattern;

use \HaploMvc\Exception\HaploCloningNotAllowedException;

abstract class HaploSingleton {
    /**
     * Holds a reference to an instance of the instantiated class
     * Used to facilitate implementation of the class as a singleton
     *
     * @var object
     **/
    protected static $instances;

    public static function reset() {
        $class = get_called_class();
        unset(self::$instances[$class]);
    }
    
    public function __clone() {
        throw new HaploCloningNotAllowedException('Cloning of '.get_called_class().' not allowed.');
    }
}