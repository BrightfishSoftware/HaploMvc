<?php
namespace HaploMvc\Db;

use PDO;

/**
 * Class HaploPostgreSqlDbDriver
 * @package HaploMvc
 */
class HaploPostgreSqlDbDriver extends HaploDbDriver
{
    /** @var string */
    protected $driverName = 'postgresql';

    /**
     * @return string
     */
    public function getDsn()
    {
        return sprintf('pgsql:dbname=%s;host=%s', $this->params['database'], $this->params['host']);
    }

    /**
     * @return array
     */
    public function getDefaultParams()
    {
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
    public function connect()
    {
        return new PDO(
            $this->getDsn(),
            $this->params['user'],
            $this->params['pass'],
            $this->driverOptions
        );
    }
}
