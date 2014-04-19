<?php
namespace HaploMvc\Debug;

use HaploMvc\HaploApp;

/**
 * Class HaploLog
 * @package HaploMvc
 */
class HaploLog
{
    protected $app;

    public function __construct(HaploApp $app) {
        $this->app = $app;
    }

    /**
     * @param string $msg
     */
    public function logError($msg)
    {
        if ($this->app->config->getKey('logging', 'logErrors', true)) {
            error_log($msg, 3, $this->app->config->getKey('logging', 'errorFile'));
        }
    }

    /**
     * @param string $msg
     */
     public function logInfo($msg)
     {
        if ($this->app->config->getKey('logging', 'logInfo', true)) {
            error_log($msg, 3, $this->app->config->getKey('logging', 'infoFile'));
        }
    }
}
