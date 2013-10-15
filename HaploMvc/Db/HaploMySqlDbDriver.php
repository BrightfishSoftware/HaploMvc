<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploMysqlDbDriver
 **/
namespace HaploMvc\Db;

use \PDO;

/**
 * Class HaploMySqlDbDriver
 * @package HaploMvc
 */
class HaploMySqlDbDriver extends HaploDbDriver {
    /** @var string */
    public $driverName = 'mysql';
    /** @var bool */
    public $hasSqlCalcFoundRows = true;

    /**
     * @return string
     */
    protected function get_dsn() {
        return sprintf(
            'mysql:dbname=%s;host=%s;charset=%s',
            $this->params['database'],
            $this->params['host'],
            $this->params['charset']
        );
    }

    /**
     * @return array
     */
    protected function get_default_params() {
        return array(
            'user' => 'root',
            'pass' => '',
            'database' => '',
            'host' => '127.0.0.1',
            'charset' => 'utf8'
        );
    }

    /**
     * @return array
     */
    protected function get_default_options() {
        return array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$this->params['charset'],
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function get_limit($limit = null, $offset = null) {
        if (!is_null($offset) && !is_null($limit)) {
            return sprintf('LIMIT %d, %d', (int)$offset, (int)$limit);
        } elseif (!is_null($limit)) {
            return 'LIMIT '.(int)$limit;
        } else {
            return '';
        }
    }

    /**
     * @return PDO
     */
    public function connect() {
        return new PDO(
            $this->get_dsn(),
            $this->params['user'],
            $this->params['pass'],
            $this->driverOptions
        );
    }
}