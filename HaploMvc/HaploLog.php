<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploLog
 **/

namespace HaploMvc;

/**
 * Class HaploLog
 * @package HaploMvc
 */
class HaploLog {
    /**
     * @param string $msg
     * @param HaploConfig $config
     */
    static public function log_error($msg, HaploConfig $config = null) {
        if (!$config instanceof HaploConfig) {
            $config = HaploConfig::get_instance();
        }
        if ($config->get_key('logging', 'logErrors', true)) {
            error_log($msg, 3, $config->get_key('logging', 'errorFile'));
        }
    }

    /**
     * @param string $msg
     * @param HaploConfig $config
     */
    static public function log_info($msg, HaploConfig $config = null) {
        if (!$config instanceof HaploConfig) {
            $config = HaploConfig::get_instance();
        }
        if ($config->get_key('logging', 'logInfo', true)) {
            error_log($msg, 3, $config->get_key('logging', 'infoFile'));
        }
    }
}