<?php
namespace HaploMvc\Db;

use PDO;

/**
 * Class HaploMySqlDbDriver
 * @package HaploMvc
 */
class HaploMySqlDbDriver extends HaploDbDriver
{
    /** @var string */
    public $driverName = 'mysql';
    /** @var bool */
    public $hasSqlCalcFoundRows = true;

    /**
     * @return string
     */
    protected function getDsn()
    {
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
    protected function getDefaultParams()
    {
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
    protected function getDefaultOptions()
    {
        return array(
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getLimit($limit = null, $offset = null)
    {
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
