<?php
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
     * @param array $params
     * @return string
     */
    protected function get_dsn(array $params) {
        return sprintf('sqlite:%s', $params['file']);
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
            !empty($this->driverOptions) ? $this->driverOptions : $this->get_default_options()
        );
    }
}