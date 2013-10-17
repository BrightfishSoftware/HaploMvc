<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploDb
 **/

namespace HaploMvc\Db;

use \PDO,
    \PDOException,
    \Exception,
    \HaploMvc\Pattern\HaploSingleton,
    \HaploMvc\Debug\HaploLog;

/**
 * Class HaploDb
 * @package HaploMvc
 */
class HaploDb extends HaploSingleton {
    /** @var HaploDbDriver */
    public $driver = null;
    /** @var PDO */
    protected $db = null;
    /** @var bool */
    protected $useSqlCalcFoundRows = false;
    /** @var int */
    protected $lastRowCount = null;

    /**
     * @param HaploDbDriver $driver
     */
    protected function __construct(HaploDbDriver $driver) {
        $this->driver = $driver;
    }

    /**
     * @return bool
     */
    protected function connect() {
        try {
            $this->db = $this->driver->connect();
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->log_error($e);
            
            return false;
        }
        
        return true;
    }

    /**
     * @param Exception $e
     */
    protected function log_error(Exception $e) {
        HaploLog::log_error(sprintf(
            'DB Error (Msg: %s - Code: %d) on line %d in %s', 
            $e->getMessage(), 
            $e->getCode(),
            $e->getLine(),
            $e->getFile()
        ));
    }

    /**
     * @param HaploDbDriver $driver
     * @return mixed
     */
    public static function get_instance(HaploDbDriver $driver = null) {
        $class = get_called_class();
        $instanceKey = $class.$driver->get_instance_hash();
        if (!isset(self::$instances[$instanceKey])) {
            self::$instances[$instanceKey] = new $class($driver);
        }
        return self::$instances[$instanceKey];
    }
    
    // make all PDO functions available directly to class
    /**
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params) {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        return call_user_func_array(array($this->db, $name), $params);
    }

    /**
     * @throws Exception
     */
    public function __clone() {
        throw new Exception('Cloning is not allowed.');
    }

    /**
     * @param string $stmt
     * @param array $params
     * @param int $start
     * @param int $count
     * @param bool $asObjects
     * @return bool|array|object
     */
    public function get_array($stmt, array $params = array(), $start = 0, $count = 0, $asObjects = false) {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        $this->lastRowCount = null;

        try {
            if ($count > 0) {
                if ($this->useSqlCalcFoundRows) {
                    $stmt = preg_replace(
                        '/^SELECT\s+/i',
                        'SELECT SQL_CALC_FOUND_ROWS ',
                        sprintf('%s %s', trim($stmt), $this->driver->get_limit($count, $start))
                    );
                } else {
                    $countStmt = preg_replace(
                        '/^select.*from/is',
                        'SELECT COUNT(*) FROM ',
                        trim($stmt)
                    );
                    $this->lastRowCount = $this->get_column($countStmt, $params);
                    $stmt = sprintf('%s %s', trim($stmt), $this->driver->get_limit($count, $start));
                }
            }
        
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result->fetchAll($asObjects ? PDO::FETCH_CLASS : PDO::FETCH_ASSOC);
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt->fetchAll($asObjects ? PDO::FETCH_CLASS : PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->log_error($e);
        }
        
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @param bool $asObject
     * @return bool|array|object
     */
    public function get_row($stmt, array $params = array(), $asObject = false) {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        try {
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $asObject ? $result->fetchObject() : $result->fetch(PDO::FETCH_ASSOC);
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $asObject ? $stmt->fetchObject() : $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->log_error($e);
        }
        
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @param int $column
     * @return bool
     */
    public function get_column($stmt, array $params = array(), $column = 0) {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        try {
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result->fetchColumn($column);
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt->fetchColumn($column);
            }
        } catch (PDOException $e) {
            $this->log_error($e);
        }
        
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @param int $start
     * @param int $count
     * @return bool
     */
    public function get_recordset($stmt, array $params = array(), $start = 0, $count = 0) {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        try {
            if ($count > 0) {
                $stmt = sprintf('%s %s', $stmt, $this->driver->get_limit($count, $start));
            }
        
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result;
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt;
            }
        } catch (PDOException $e) {
            $this->log_error($e);
        }
        
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @return bool
     */
    public function run($stmt, array $params = array()) {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        try {
            if (empty($params)) {
                return $this->db->query($stmt);
            } elseif ($stmt = $this->db->prepare($stmt)) {
                return $stmt->execute($params);
            }
        } catch (PDOException $e) {
            $this->log_error($e);
        }
        
        return false;
    }

    /**
     * @param array $values
     * @return mixed
     */
    public function get_in_values(array $values) {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        foreach ($values as &$value) {
            $value = $this->db->quote($value);
        }

        return $values;
    }

    /**
     * @return bool|int
     */
    public function get_total_rows() {
        if (is_null($this->db) && !$this->connect()) {
            return 0;
        }

        if ($this->useSqlCalcFoundRows) {
            try {
                if ($result = $this->db->query('SELECT FOUND_ROWS()')) {
                    return $result->fetchColumn(0);
                }
            } catch (PDOException $e) {
                $this->log_error($e);
            }
        } else {
            return $this->lastRowCount;
        }
        
        return false;
    }

    /**
     * @param int $page
     * @param int $numPerPage
     * @return array
     */
    public function get_offsets_from_page($page, $numPerPage = 50) {
        $start = ($page - 1) * $numPerPage;
        
        return array($start, $numPerPage);
    }

    /**
     * @param int $page
     * @param int $numPerPage
     * @param int $numEitherSide
     * @return array|bool
     */
    public function get_paging($page, $numPerPage = 50, $numEitherSide = 4) {
        $numRows = $this->get_total_rows();
        $numPages = ceil($numRows / $numPerPage);
        
        if ($page < 1 || $page > $numPages) {
            return false;
        }
        
        $pages = array();
        
        $pages['total'] = $numRows;
        $pages['previous'] = ($page > 1);
        $pages['previous_n'] = (($page - $numEitherSide) > 1);
        
        if ($page <= $numEitherSide + 1) {
            $pages['start'] = 1;
        } elseif ($numPages - $page < $numEitherSide) {
            $pages['start'] = $page - ($numEitherSide * 2 - ($numPages - $page));
            
            if ($pages['start'] < 1) {
                $pages['start'] = 1;
            }
        } else {
            $pages['start'] = $page - $numEitherSide;
        }
        
        if ($page >= $numPages - $numEitherSide) {
            $pages['end'] = $numPages;
        } elseif ($page <= $numEitherSide) {
            $pages['end'] = $page + ($numEitherSide * 2 + 1 - $page);
            
            if ($pages['end'] > $numPages) {
                $pages['end'] = $numPages;
            }
        } else {
            $pages['end'] = $page + $numEitherSide;
        }
        
        $pages['next_n'] = (($page + $numEitherSide) < $numPages);
        $pages['next'] = ($page < $numPages);
        $pages['current'] = $page;
        $pages['num_pages'] = $numPages;
        $pages['first_result'] = ($pages['current'] - 1) * $numPerPage + 1;
        $pages['last_result'] = ($pages['current'] - 1) * $numPerPage + $numPerPage;
        
        if ($pages['last_result'] > $pages['total']) {
            $pages['last_result'] = $pages['total'];
        }
        
        return $pages;
    }

    /**
     * @param bool $useSqlCalcFoundRows
     */
    public function set_use_sql_calc_found_rows($useSqlCalcFoundRows) {
        if ($this->driver->driverName === 'mysql') {
            $this->useSqlCalcFoundRows = $useSqlCalcFoundRows;
        } else {
            $this->useSqlCalcFoundRows = false;
        }
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function quote_identifier($identifier) {
        $parts = explode('.', $identifier);
        foreach ($parts as &$part) {
            $part = '`'.trim(str_replace('`', '``', $part)).'`';
        }
        return implode('.', $parts);
    }
}