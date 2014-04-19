<?php
namespace HaploMvc\Db;

use PDO;
use PDOException;
use Exception;
use HaploMvc\HaploApp;

/**
 * Class HaploDb
 * @package HaploMvc
 */
class HaploDb
{
    /** @var HaploApp */
    public $app = null;
    /** @var HaploDbDriver */
    public $driver = null;
    /** @var PDO */
    protected $db = null;
    /** @var bool */
    protected $useSqlCalcFoundRows = false;
    /** @var int */
    protected $lastRowCount = null;

    /**
     * @param HaploApp $app
     * @param HaploDbDriver $driver
     */
    public function __construct(HaploApp $app, HaploDbDriver $driver)
    {
        $this->app = $app;
        $this->driver = $driver;
    }

    /**
     * @return bool
     */
    protected function connect()
    {
        try {
            $this->db = $this->driver->connect();
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->logError($e);
            
            return false;
        }
        
        return true;
    }

    /**
     * @param Exception $e
     */
    protected function logError(Exception $e)
    {
        $this->app->log->logError(sprintf(
            'DB Error (Msg: %s - Code: %d) on line %d in %s', 
            $e->getMessage(), 
            $e->getCode(),
            $e->getLine(),
            $e->getFile()
        ));
    }
    
    // make all PDO functions available directly to class
    /**
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        return call_user_func_array(array($this->db, $name), $params);
    }

    /**
     * @throws Exception
     */
    public function __clone()
    {
        throw new Exception('Cloning is not allowed.');
    }

    /**
     * @param string $stmt
     * @param array $params
     * @param int $start
     * @param int $count
     * @return bool|object
     */
    public function getArray($stmt, array $params = array(), $start = 0, $count = 0)
    {
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
                        sprintf('%s %s', trim($stmt), $this->driver->getLimit($count, $start))
                    );
                } else {
                    $countStmt = preg_replace(
                        '/^SELECT.*FROM/is',
                        'SELECT COUNT(*) FROM ',
                        trim($stmt)
                    );
                    $this->lastRowCount = $this->getColumn($countStmt, $params);
                    $stmt = sprintf('%s %s', trim($stmt), $this->driver->getLimit($count, $start));
                }
            }
        
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result->fetchAll(PDO::FETCH_CLASS);
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt->fetchAll(PDO::FETCH_CLASS);
            }
        } catch (PDOException $e) {
            $this->logError($e);
        }
        
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @param int $page
     * @param int $numPerPage
     * @param int $numEitherSide
     * @return array
     */
    public function getPagedArray($stmt, array $params, $page = 1, $numPerPage = 50, $numEitherSide = 4)
    {
        list($start, $count) = $this->getOffsetsFromPage($page, $numPerPage);
        $results = $this->getArray($stmt, $params, $start, $count);
        $paging = $this->getPaging($page, $numPerPage, $numEitherSide);
        return array($results, $paging);
    }

    /**
     * @param string $sql
     * @param array $params
     *
     * @return array
     */
    public function getList($sql, array $params = array())
    {
        if (($results = $this->getArray($sql, $params)) && is_array($results)) {
            $list = array();
            foreach ($results as $result) {
                $list[] = current($result);
            }
            return $list;
        }
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @return bool|object
     */
    public function getRow($stmt, array $params = array())
    {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        try {
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result->fetchObject();
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt->fetchObject();
            }
        } catch (PDOException $e) {
            $this->logError($e);
        }
        
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @param int $column
     * @return bool
     */
    public function getColumn($stmt, array $params = array(), $column = 0)
    {
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
            $this->logError($e);
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
    public function getRecordset($stmt, array $params = array(), $start = 0, $count = 0)
    {
        if (is_null($this->db) && !$this->connect()) {
            return false;
        }

        try {
            if ($count > 0) {
                $stmt = sprintf('%s %s', $stmt, $this->driver->getLimit($count, $start));
            }
        
            if (empty($params) && $result = $this->db->query($stmt)) {
                return $result;
            } elseif (($stmt = $this->db->prepare($stmt)) && $stmt->execute($params)) {
                return $stmt;
            }
        } catch (PDOException $e) {
            $this->logError($e);
        }
        
        return false;
    }

    /**
     * @param string $stmt
     * @param array $params
     * @return bool
     */
    public function run($stmt, array $params = array())
    {
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
            $this->logError($e);
        }
        
        return false;
    }

    /**
     * @param array $values
     * @return mixed
     */
    public function getInValues(array $values)
    {
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
    public function getTotalRows()
    {
        if (is_null($this->db) && !$this->connect()) {
            return 0;
        }

        if ($this->useSqlCalcFoundRows) {
            try {
                if ($result = $this->db->query('SELECT FOUND_ROWS()')) {
                    return $result->fetchColumn(0);
                }
            } catch (PDOException $e) {
                $this->logError($e);
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
    public function getOffsetsFromPage($page, $numPerPage = 50)
    {
        if ($page === 0 && $numPerPage === 0) {
            return array(0, 0);
        }
        $start = ($page - 1) * $numPerPage;
        return array($start, $numPerPage);
    }

    /**
     * @param int $page
     * @param int $numPerPage
     * @param int $numEitherSide
     * @return array|bool
     */
    public function getPaging($page, $numPerPage = 50, $numEitherSide = 4)
    {
        if ($page === 0 && $numPerPage === 0) {
            return false;
        }

        $numRows = $this->getTotalRows();
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
    public function setUseSqlCalcFoundRows($useSqlCalcFoundRows)
    {
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
    public function quoteIdentifier($identifier)
    {
        $parts = explode('.', $identifier);
        foreach ($parts as &$part) {
            $part = '`'.trim(str_replace('`', '``', $part)).'`';
        }
        return implode('.', $parts);
    }
}
