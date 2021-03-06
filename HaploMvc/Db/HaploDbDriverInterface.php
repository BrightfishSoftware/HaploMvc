<?php
namespace HaploMvc\Db;

/**
 * Class HaploDbDriverInterface
 * @package HaploMvc
 */
interface HaploDbDriverInterface
{
    /**
     * @param array $params
     * @param array $driverOptions
     */
    public function __construct(array $params, array $driverOptions = array());

    /**
     * @return mixed
     */
    public function connect();

    /**
     * @return mixed
     */
    public function getLimit();

    /**
     * @return mixed
     */
    public function getInstanceHash();
}
