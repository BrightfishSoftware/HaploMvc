<?php
/**
 * Copyright (C) 2008-2013, Brightfish Software Limited
 * @package HaploDb
 **/

namespace HaploMvc;

use \PDO,
    \PDOException,
    \Exception;

/**
 * Class HaploDb
 * @package HaploMvc
 */
class HaploDb extends HaploSingleton {
    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_POSTGRESQL = 'postgresql';
    const DB_TYPE_SQLITE = 'sqlite';

    /** @var string */
    protected $dbType;
    /** @var PDO */
    protected $db;
    /** @var bool */
    protected $useSqlCalcFoundRows = false;
    /** @var int */
    protected $lastRowCount = null;

    /**
     * @param $params
     */
    protected function __construct($params) {
        $this->connect($params);
    }

    /**
     * @param array $params
     * @param string $dbType
     * @param array $driverOptions
     * @return bool
     */
    protected function connect(array $params, $dbType, array $driverOptions) {
        $this->dbType = $dbType;

        switch ($this->dbType) {
            case self::DB_TYPE_MYSQL:
                $dsn = sprintf('mysql:dbname=%s;host=%s', $params['database'], $params['host']);
                if (is_null($driverOptions)) {
                    $driverOptions = array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
                        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                    );
                }
                break;
            case self::DB_TYPE_POSTGRESQL:
                $dsn = sprintf('pgsql:dbname=%s;host=%s', $params['database'], $params['host']);
                if (is_null($driverOptions)) {
                    $driverOptions = array();
                }
                break;
            case self::DB_TYPE_SQLITE:
                $dsn = sprintf('sqlite:%s', $params['file']);
                if (is_null($driverOptions)) {
                    $driverOptions = array(
                        PDO::ATTR_PERSISTENT => true
                    );
                }
                break;
            default:
                return false;
        }
        
        try {
            switch ($this->dbType) {
                case self::DB_TYPE_MYSQL:
                case self::DB_TYPE_POSTGRESQL:
                    $this->db = new PDO($dsn, $params['user'], $params['pass'], $driverOptions);
                    break;
                case self::DB_TYPE_SQLITE:
                    $this->db = new PDO($dsn, null, null, $driverOptions);
                    break;
            }

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
     * @param array $params
     * @param string $dbType
     * @param array $driverOptions
     * @return mixed
     */
    public static function get_instance(
        array $params = array(),
        $dbType = self::DB_TYPE_MYSQL,
        array $driverOptions = null
    ) {
        $defaultParams = array(
            self::DB_TYPE_MYSQL => array(
                'user' => 'root',
                'pass' => '',
                'database' => '',
                'host' => '127.0.0.1'
            ),
            self::DB_TYPE_POSTGRESQL => array(
                'user' => '',
                'pass' => '',
                'database' => '',
                'host' => '',
                'port' => ''
            ),
            self::DB_TYPE_SQLITE => array(
                'file' => ':memory:'
            )
        );
        $params = array_merge($defaultParams[$dbType], $params);
        $class = get_called_class();
        $instanceKey = sha1($class.$dbType.serialize($params).serialize($driverOptions));
        
        if (!isset(self::$instances[$instanceKey])) {
            self::$instances[$instanceKey] = new $class($params, $dbType, $driverOptions);
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
     * @return bool
     */
    public function get_array($stmt, array $params = array(), $start = 0, $count = 0) {
        $this->lastRowCount = null;

        try {
            if ($count > 0) {
                if ($this->useSqlCalcFoundRows) {
                    $stmt = preg_replace(
                        '/^select\s+/i', 
                        'select sql_calc_found_rows ', 
                        sprintf('%s limit %d, %d', trim($stmt), $start, $count)
                    );
                } else {
                    $countStmt = preg_replace(
                        '/^select.*from/is',
                        'select count(*) from ',
                        trim($stmt)
                    );
                    $this->lastRowCount = $this->get_column($countStmt, $params);
                    $stmt =sprintf('%s limit %d offset %d', trim($stmt), $count, $start);
                }
            }
        
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result->fetchAll(PDO::FETCH_ASSOC);
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    public function get_row($stmt, array $params = array()) {
        try {
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result->fetch(PDO::FETCH_ASSOC);
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
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
        try {
            if ($count > 0) {
                $stmt = sprintf('%s limit %d, %d', $stmt, $start, $count);
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
        foreach ($values as &$value) {
            $value = $this->db->quote($value);
        }

        return $values;
    }

    /**
     * @return bool|int
     */
    public function get_total_rows() {
        if ($this->useSqlCalcFoundRows) {
            try {
                if ($result = $this->db->query('select found_rows()')) {
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
        if ($this->dbType == self::DB_TYPE_MYSQL) {
            $this->useSqlCalcFoundRows = $useSqlCalcFoundRows;
        } else {
            $this->useSqlCalcFoundRows = false;
        }
    }
}