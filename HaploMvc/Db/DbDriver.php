<?php
namespace HaploMvc\Db;

/**
 * Class DbDriver
 * @package HaploMvc
 */
abstract class DbDriver implements DbDriverInterface
{
    /** @var array */
    protected $params;
    /** @var $driverOptions */
    protected $driverOptions;
    /** @var string */
    public $driverName = 'generic';
    /** @var bool */
    public $hasSqlCalcFoundRows = false;

    /**
     * @param array $params
     * @param array $driverOptions
     */
    public function __construct(array $params = [], array $driverOptions = [])
    {
        $this->params = array_merge($this->getDefaultParams(), $params);
        $this->driverOptions = array_merge($this->getDefaultOptions(), $driverOptions);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function getLimit($limit = null, $offset = null)
    {
        if (!is_null($offset) && !is_null($limit)) {
            return sprintf('LIMIT %d OFFSET %d', (int)$limit, (int)$offset);
        } elseif (!is_null($limit)) {
            return 'LIMIT '.(int)$limit;
        } else {
            return '';
        }
    }

    protected function getDefaultParams()
    {
        return [];
    }

    protected function getDefaultOptions()
    {
        return [];
    }
}
