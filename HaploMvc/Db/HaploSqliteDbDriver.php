<?php
namespace HaploMvc\Db;

use PDO;

/**
 * Class HaploSqliteDbDriver
 * @package HaploMvc
 */
class HaploSqliteDbDriver extends HaploDbDriver
{
    /** @var string */
    public $driverName = 'sqlite';

    /**
     * @return string
     */
    protected function getDsn()
    {
        return sprintf('sqlite:%s', $this->params['file']);
    }

    /**
     * @return array
     */
    protected function getDefaultParams()
    {
        return array(
            'file' => ':memory:'
        );
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            PDO::ATTR_PERSISTENT => true
        );
    }

    /**
     * @return PDO
     */
    public function connect()
    {
        return new PDO(
            $this->getDsn($this->params),
            null,
            null,
            $this->driverOptions
        );
    }
}
