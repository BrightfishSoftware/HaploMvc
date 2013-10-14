<?php
namespace HaploMvc\Db;

/**
 * Class HaploDbDriverInterface
 * @package HaploMvc
 */
interface HaploDbDriverInterface {
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
    public function get_limit();

    /**
     * @return mixed
     */
    public function get_instance_hash();
}