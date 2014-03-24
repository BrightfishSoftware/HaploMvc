<?php
namespace HaploMvc\Debug;

use HaploMvc\HaploApp;

/**
 * Class HaploLog
 * @package HaploMvc
 */
class HaploLog
{
    /**
     * @param string $msg
     * @param HaploApp $app
     */
    public static function logError($msg, HaploApp $app = null)
    {
        if (!$app instanceof HaploApp) {
            $app = HaploApp::getInstance();
        }
        if ($app->config->getKey('logging', 'logErrors', true)) {
            error_log($msg, 3, $app->config->getKey('logging', 'errorFile'));
        }
    }

    /**
     * @param string $msg
     * @param HaploApp $app
     */
     public static function logInfo($msg, HaploApp $app = null)
     {
        if (!$app instanceof HaploApp) {
            $app = HaploApp::getInstance();
        }
        if ($app->config->getKey('logging', 'logInfo', true)) {
            error_log($msg, 3, $app->config->getKey('logging', 'infoFile'));
        }
    }
}
