<?php
namespace HaploMvc\Db;

use HaploMvc\Exception\HaploDbIdNotFoundException;
use HaploMvc\Exception\HaploDbColumnDoesNotExistException;
use HaploMvc\Exception\HaploDbTableNameNotSetException;

/**
 * Class HaploActiveRecord
 * @package HaploMvc\Db
 */
abstract class HaploActiveRecord
{
    /** @var HaploDb */
    protected static $db = null;
    /** @var HaploSqlBuilder */
    protected static $sqlBuilder = null;
    /** @var array */
    protected $fields = array();
    /** @var int */
    public $id = null;
    /** @var bool */
    public $dirty = false;

    /**
     * @param HaploDb $db
     * @param HaploSqlBuilder $sqlBuilder
     */
    public static function setDependencies(HaploDb $db, HaploSqlBuilder $sqlBuilder)
    {
        self::$db = $db;
        self::$sqlBuilder = $sqlBuilder;
    }

    /**
     * @throws HaploDbTableNameNotSetException
     */
    public static function tableName()
    {
        throw new HaploDbTableNameNotSetException(sprintf('Table name not set in %s.', get_called_class()));
    }

    /**
     * @return string
     */
    public static function primaryKey()
    {
        return static::tableName().'_id';
    }

    /**
     * @param int $id
     */
    public function __construct($id = null)
    {
        if (!is_null($id)) {
            $this->load($id);
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws HaploDbColumnDoesNotExistException
     */
    public function __set($name, $value)
    {
        $field = $this->getFieldFromProperty($name);
        if (!is_null($this->id) && !array_key_exists($field, $this->fields)) {
            throw new HaploDbColumnDoesNotExistException(sprintf('Column %s does not exist in table %s.', $field, static::tableName()));
        }
        $setter = 'set'.ucfirst($name);
        $this->fields[$field] = method_exists($this, $setter) ? $this->$setter($value) : $value;
        $this->dirty = true;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws HaploDbColumnDoesNotExistException
     */
    public function __get($name)
    {
        $field = $this->getFieldFromProperty($name);
        if ($field === static::primaryKey()) {
            return $this->id;
        }
        if (!is_null($this->id) && !array_key_exists($field, $this->fields)) {
            throw new HaploDbColumnDoesNotExistException(sprintf('Column %s does not exist in table %s.', $field, $this->tableName()));
        }
        return array_key_exists($field, $this->fields) ? $this->fields[$field] : null;
    }

    /**
     * @param int $id
     * @throws HaploDbIdNotFoundException
     */
    protected function load($id)
    {
        $this->id = $id;
        $sql = self::$sqlBuilder->where(static::primaryKey(), '=', $this->id)
            ->get(static::tableName());
        $this->fields = static::$db->getRow($sql);
        if (empty($this->fields)) {
            throw new HaploDbIdNotFoundException(sprintf('ID %d not found in %.', $this->id, static::tableName()));
        }
        unset($this->fields[static::primaryKey()]);
    }

    /**
     * @return bool|int
     */
    public function save()
    {
        if (!$this->dirty) {
            return false;
        }
        if (!is_null($this->id)) { // update
            self::$sqlBuilder->where(static::primaryKey(), '=', $this->id);
            $sql = self::$sqlBuilder->update(static::tableName(), $this->fields);
        } else { // insert
            $sql = self::$sqlBuilder->insert(static::tableName(), $this->fields);
        }
        $this->dirty = false;
        if ($result = static::$db->run($sql)) {
            $this->id = static::$db->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        if (is_null($this->id)) {
            return false;
        }
        $sql = self::$sqlBuilder->where(static::primaryKey(), '=', $this->id)
            ->delete(static::tableName());
        static::$db->run($sql);
        $this->id = null;
    }

    /**
     * @param string $field
     * @return string
     */
    protected function getFieldFromProperty($field) {
        return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $field));
    }

    /**
     * @param string $name
     * @param array $args
     * @return HaploActiveRecord|bool
     */
    public static function __callStatic($name, $args)
    {
        if (substr($name, 0, strlen('find_by_')) === 'find_by_') {
            return static::findBy($name, $args);
        }
        return false;
    }

    /**
     * Alias to calling new Class($id) directly
     *
     * @param int $id
     * @return mixed
     */
    public static function find($id)
    {
        $class = get_called_class();
        return new $class($id);
    }

    /**
     * @param string $sql
     * @param array $params
     * @param int $page
     * @param int $numPerPage
     * @return array|bool
     */
    public static function findBySql($sql, $params = array(), $page = 0, $numPerPage = 0)
    {
        $params = static::formatBindParams($params);
        list($start, $count) = self::$db->getOffsetsFromPage($page, $numPerPage);
        $results = self::$db->getArray($sql, $params, $start, $count);
        $paging = self::$db->getPaging($page, $numPerPage);
        $objects = array();
        foreach ($results as $result) {
            $objects[] = static::hydrate($result);
        }
        return $page !== 0 && $numPerPage !== 0 ? array($objects, $paging) : $objects;
    }

    /**
     * @param $sql
     * @param array $params
     * @return bool|object
     */
    public static function findOneBySql($sql, $params = array())
    {
        $params = static::formatBindParams($params);
        $result = self::$db->getRow($sql, $params);
        return !empty($result) ? static::hydrate($result) : false;
    }

    /**
     * @param string $name
     * @param array $args
     * @return bool|HaploActiveRecord
     */
    protected static function findBy($name, $args)
    {
        /** @var object $result */
        $result = self::$sqlBuilder->where(str_replace($name, 'find_by_', ''), '=', $args[0])
            ->get(static::tableName());
        if (!empty($result)) {
            return static::hydrate($result);
        } else {
            return false;
        }
    }

    /**
     * @param object $result
     * @return HaploActiveRecord
     */
    protected static function hydrate($result)
    {
        $primaryKey = static::primaryKey();
        $class = get_called_class();
        /** @var HaploActiveRecord $object */
        $object = new $class;
        foreach (get_object_vars($result) as $key => $value) {
            if ($key !== $primaryKey) {
                $object->$key = $value;
            }
        }
        $object->id = $result->$primaryKey;
        return $object;
    }

    /**
     * @param array $params
     * @return array
     */
    protected static function formatBindParams(array $params)
    {
        $processed = array();
        foreach ($params as $key => $value) {
            $processed[':'.$key] = $value;
        }
        return $processed;
    }
}
