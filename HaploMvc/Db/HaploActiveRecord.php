<?php
namespace HaploMvc\Db;

use HaploMvc\HaploApp;
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
     * @param HaploApp $app;
     */
    public static function setDependencies(HaploApp $app)
    {
        static::$db = $app->db;
        static::$sqlBuilder = new HaploSqlBuilder(static::$db);
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
        $field = static::camelCaseToUnderscore($name);
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
        $field = static::camelCaseToUnderscore($name);
        if ($field === static::primaryKey()) {
            return $this->id;
        }
        if (!is_null($this->id) && !array_key_exists($field, $this->fields)) {
            throw new HaploDbColumnDoesNotExistException(sprintf('Column %s does not exist in table %s.', $field, static::tableName()));
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
        $sql = static::$sqlBuilder->where(static::primaryKey(), '=', $this->id)
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
            static::$sqlBuilder->where(static::primaryKey(), '=', $this->id);
            $sql = static::$sqlBuilder->update(static::tableName(), $this->fields);
        } else { // insert
            $sql = static::$sqlBuilder->insert(static::tableName(), $this->fields);
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
        $sql = static::$sqlBuilder->where(static::primaryKey(), '=', $this->id)
            ->delete(static::tableName());
        static::$db->run($sql);
        $this->id = null;
    }

    /**
     * @param string $field
     * @return string
     */
    protected static function camelCaseToUnderscore($field) {
        return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $field));
    }

    /**
     * @param string $name
     * @param array $args
     * @return HaploActiveRecord|bool
     */
    public static function __callStatic($name, $args)
    {
        if (substr($name, 0, strlen('findBy')) === 'findBy') {
            return static::findBy($name, $args);
        } elseif (substr($name, 0, strlen('findOneBy')) === 'findOneBy') {
            return static::findOneBy($name, $args);
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
        list($start, $count) = static::$db->getOffsetsFromPage($page, $numPerPage);
        $results = static::$db->getArray($sql, $params, $start, $count);
        $paging = static::$db->getPaging($page, $numPerPage);
        $objects = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $objects[] = static::hydrate($result);
            }
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
        $result = static::$db->getRow($sql, $params);
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
        $name = str_replace(static::camelCaseToUnderscore($name), 'find_by_', '');
        $sql = static::$sqlBuilder->where($name, '=', $args[0])
            ->get(static::tableName());
        $results = static::$db->getArray($sql);
        $objects = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $objects[] = static::hydrate($result);
            }
            return $objects;
        }
        return false;
    }

    /**
     * @param string $name
     * @param array $args
     * @return bool|HaploActiveRecord
     */
    protected static function findOneBy($name, $args)
    {
        /** @var object $result */
        $name = str_replace(static::camelCaseToUnderscore($name), 'find_one_by_', '');
        $sql = static::$sqlBuilder->where($name, '=', $args[0])
            ->get(static::tableName());
        $result = static::$db->getRow($sql);
        if (!empty($result)) {
            return static::hydrate($result);
        }
        return false;
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
