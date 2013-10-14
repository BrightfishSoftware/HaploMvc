<?php
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
     * @param array $params
     * @return string
     */
    public function get_dsn(array $params) {
        return sprintf('pgsql:dbname=%s;host=%s', $params['database'], $params['host']);
    }

    /**
     * @return array
     */
    public function get_default_params() {
        return array(
            'user' => '',
            'pass' => '',
            'database' => '',
            'host' => '',
            'port' => ''
        );
    }

    /**
     * @return array
     */
    public function get_default_options() {
        return array();
    }

    /**
     * @return PDO
     */
    public function connect() {
        return new PDO(
            $this->get_dsn($this->params),
            $this->params['user'],
            $this->params['pass'],
            !empty($this->driverOptions) ? $this->driverOptions : $this->get_default_options()
        );
    }
}