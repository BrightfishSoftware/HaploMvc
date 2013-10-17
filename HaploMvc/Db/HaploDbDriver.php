<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploDbDriver
 **/
namespace HaploMvc\Db;

/**
 * Class HaploDbDriver
 * @package HaploMvc
 */
abstract class HaploDbDriver implements HaploDbDriverInterface {
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
    public function __construct(array $params = array(), array $driverOptions = array()) {
        $this->params = array_merge($this->get_default_params(), $params);
        $this->driverOptions = array_merge($this->get_default_options(), $driverOptions);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function get_limit($limit = null, $offset = null) {
        if (!is_null($offset) && !is_null($limit)) {
            return sprintf('LIMIT %d OFFSET %d', (int)$limit, (int)$offset);
        } elseif (!is_null($limit)) {
            return 'LIMIT '.(int)$limit;
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function get_instance_hash() {
        return sha1($this->driverName.serialize($this->params).serialize($this->driverOptions));
    }

    protected function get_default_params() {
        return array();
    }

    protected function get_default_options() {
        return array();
    }
}