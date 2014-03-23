<?php
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
