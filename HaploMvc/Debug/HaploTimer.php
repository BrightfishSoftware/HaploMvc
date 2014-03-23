<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploTimer
 **/

namespace HaploMvc\Debug;

class HaploTimer
{
    protected static $startTime;
    
    /**
     * Start timing script
     **/
    public static function start()
    {
        self::$startTime = microtime(true);
    }

    public static function get()
    {
        return (microtime(true) - self::$startTime);
    }
}
