<?php
namespace HaploMvc\Db;

use PDO;

/**
 * Class SqliteDbDriver
 * @package HaploMvc
 */
class SqliteDbDriver extends DbDriver
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
        return [
            'file' => ':memory:'
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            PDO::ATTR_PERSISTENT => true
        ];
    }

    /**
     * @return PDO
     */
    public function connect()
    {
        return new PDO(
            $this->getDsn(),
            null,
            null,
            $this->driverOptions
        );
    }
}
