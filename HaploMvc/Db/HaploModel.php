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
    protected $builder;

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
            $engine = $config->get_key($dbConfig, 'engine', 'MySql');
            $driver = sprintf('\\HaploMvc\\Db\\Haplo%sDbDriver', $engine);
            $this->db = HaploDb::get_instance(new $driver($config->get_section($dbConfig)));
        }
        $this->builder = new HaploQueryBuilder($this->db);
    }
}