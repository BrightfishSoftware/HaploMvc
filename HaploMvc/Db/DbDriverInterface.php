<?php
namespace HaploMvc\Db;

/**
 * Class DbDriverInterface
 * @package HaploMvc
 */
interface DbDriverInterface
{
    /**
     * @param array $params
     * @param array $driverOptions
     */
    public function __construct(array $params, array $driverOptions = []);

    /**
     * @return mixed
     */
    public function connect();

    /**
     * @return mixed
     */
    public function getLimit();
}
