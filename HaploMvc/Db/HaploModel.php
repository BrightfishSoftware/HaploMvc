<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploModel
 **/

namespace HaploMvc\Db;

use \HaploMvc\HaploApp;

/**
 * Class HaploModel
 * @package HaploMvc
 */
abstract class HaploModel {
    /** @var HaploDb */
    protected $app;
    protected $db;
    protected $builder;

    /**
     * @param HaploApp $app
     * @param string $dbConfig
     */
    public function __construct(HaploApp $app, $dbConfig = 'db') {
        $this->app = $app;
        $engine = $app->config->get_key($dbConfig, 'engine', 'MySql');
        $driver = sprintf('\HaploMvc\Db\Haplo%sDbDriver', $engine);
        $this->db = HaploDb::get_instance(new $driver($app->config->get_section($dbConfig)));
        $this->builder = new HaploQueryBuilder($this->db);
    }
}