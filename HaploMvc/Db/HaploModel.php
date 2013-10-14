<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploModel
 **/

namespace HaploMvc\Db;

use \HaploMvc\Config\HaploConfig;

/**
 * Class HaploModel
 * @package HaploMvc
 */
abstract class HaploModel {
    /** @var HaploDb */
    protected $db;

    /**
     * @param HaploDb $db
     * @param HaploConfig $config
     * @param string $dbConfig
     */
    public function __construct(HaploDb $db = null, HaploConfig $config = null, $dbConfig = 'db') {
        if (!$config instanceof HaploConfig) {
            $config = HaploConfig::get_instance();
        }

        if ($db instanceof HaploDb) {
            $this->db = &$db;
        } else {
            $this->db = HaploDb::get_instance(array(
                'user' => $config->get_key($dbConfig, 'user'),
                'pass' => $config->get_key($dbConfig, 'pass'),
                'database' => $config->get_key($dbConfig, 'database'),
                'host' => $config->get_key($dbConfig, 'host')
            ));
        }
    }
}