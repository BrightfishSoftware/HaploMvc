<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploSqliteDbDriver
 **/
namespace HaploMvc\Db;

use \PDO;

/**
 * Class HaploSqliteDbDriver
 * @package HaploMvc
 */
class HaploSqliteDbDriver extends HaploDbDriver {
    /** @var string */
    public $driverName = 'sqlite';

    /**
     * @return string
     */
    protected function get_dsn() {
        return sprintf('sqlite:%s', $this->params['file']);
    }

    /**
     * @return array
     */
    protected function get_default_params() {
        return array(
            'file' => ':memory:'
        );
    }

    /**
     * @return array
     */
    protected function get_default_options() {
        return array(
            PDO::ATTR_PERSISTENT => true
        );
    }

    /**
     * @return PDO
     */
    public function connect() {
        return new PDO(
            $this->get_dsn($this->params),
            null,
            null,
            $this->driverOptions
        );
    }
}