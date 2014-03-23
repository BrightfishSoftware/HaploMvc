<?php
namespace HaploMvc\Debug;

use HaploMvc\Config\HaploConfig;

/**
 * Class HaploLog
 * @package HaploMvc
 */
class HaploLog
{
    /**
     * @param string $msg
     * @param HaploConfig $config
     */
    public static function logError($msg, HaploConfig $config = null)
    {
        if (!$config instanceof HaploConfig) {
            $config = HaploConfig::getInstance();
        }
        if ($config->getKey('logging', 'logErrors', true)) {
            error_log($msg, 3, $config->getKey('logging', 'errorFile'));
        }
    }

    /**
     * @param string $msg
     * @param HaploConfig $config
     */
     public static function logInfo($msg, HaploConfig $config = null)
     {
        if (!$config instanceof HaploConfig) {
            $config = HaploConfig::getInstance();
        }
        if ($config->getKey('logging', 'logInfo', true)) {
            error_log($msg, 3, $config->getKey('logging', 'infoFile'));
        }
    }
}
