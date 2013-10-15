<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploPostgreSqlDbDriver
 **/
namespace HaploMvc\Db;

use \PDO;

/**
 * Class HaploPostgreSqlDbDriver
 * @package HaploMvc
 */
class HaploPostgreSqlDbDriver extends HaploDbDriver {
    /** @var string */
    protected $driverName = 'postgresql';

    /**
     * @return string
     */
    public function get_dsn() {
        return sprintf('pgsql:dbname=%s;host=%s', $this->params['database'], $this->params['host']);
    }

    /**
     * @return array
     */
    public function get_default_params() {
        return array(
            'user' => '',
            'pass' => '',
            'database' => '',
            'host' => ''
        );
    }

    /**
     * @return PDO
     */
    public function connect() {
        return new PDO(
            $this->get_dsn($this->params),
            $this->params['user'],
            $this->params['pass'],
            $this->driverOptions
        );
    }
}