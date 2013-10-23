<?php
namespace HaploMvc\Db;

use \HaploMvc\Exception\HaploDbIdNotFoundException,
    \HaploMvc\Exception\HaploDbColumnDoesNotExistException,
    \HaploMvc\Exception\HaploDbTableNameNotSetException;

/**
 * Class HaploActiveRecord
 * @package HaploMvc\Db
 */
abstract class HaploActiveRecord {
    /** @var HaploDb */
    protected static $db = null;
    /** @var HaploSqlBuilder */
    protected static $sqlBuilder = null;
    /** @var array */
    protected $fields = null;
    /** @var int */
    public $id = null;
    /** @var bool */
    public $dirty = false;

    /**
     * @param HaploDb $db
     * @param HaploSqlBuilder $sqlBuilder
     */
    public static function set_dependencies(HaploDb $db, HaploSqlBuilder $sqlBuilder = null) {
        self::$db = $db;
        self::$sqlBuilder = !is_null($sqlBuilder) ? $sqlBuilder : new HaploSqlBuilder($db);
    }

    /**
     * @throws HaploDbTableNameNotSetException
     */
    public static function table_name() {
        throw new HaploDbTableNameNotSetException(sprintf('Table name not set in %s.', get_called_class()));
    }

    /**
     * @return string
     */
    public static function primary_key() {
        return static::table_name().'_id';
    }

    /**
     * @param int $id
     */
    public function __construct($id = null) {
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
    public function __set($name, $value) {
        if (!is_null($this->id) && !array_key_exists($name, $this->fields)) {
            throw new HaploDbColumnDoesNotExistException(sprintf('Column %s does not exist in table %s.', $name, static::table_name()));
        }
        $this->fields[$name] = $value;
        $this->dirty = true;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws HaploDbColumnDoesNotExistException
     */
    public function __get($name) {
        if ($name === static::primary_key()) {
            return $this->id;
        }
        if (!is_null($this->id) && !array_key_exists($name, $this->fields)) {
            throw new HaploDbColumnDoesNotExistException(sprintf('Column %s does not exist in table %s.', $name, $this->table_name()));
        }
        return array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
    }

    /**
     * @param int $id
     * @throws HaploDbIdNotFoundException
     */
    protected function load($id) {
        $this->id = $id;
        $this->fields = self::$sqlBuilder->where(static::primary_key(), '=', $this->id)
            ->get(static::table_name());
        if (empty($this->fields)) {
            throw new HaploDbIdNotFoundException(sprintf('ID %d not found in %.', $this->id, static::table_name()));
        }
        unset($this->fields[static::primary_key()]);
    }

    /**
     * @return bool|int
     */
    public function save() {
        if (!$this->dirty) {
            return false;
        }
        if (!is_null($this->id)) { // update
            self::$sqlBuilder->where(static::primary_key(), '=', $this->id);
            $result = self::$sqlBuilder->update(static::table_name(), $this->fields);
        } else { // insert
            $result = self::$sqlBuilder->insert(static::table_name(), $this->fields);
        }
        $this->dirty = false;
        return $result;
    }

    /**
     * @return bool
     */
    public function delete() {
        if (is_null($this->id)) {
            return false;
        }
        self::$sqlBuilder->where(static::primary_key(), '=', $this->id)
            ->delete(static::table_name());
        $this->id = null;
    }

    /**
     * @param string $name
     * @param array $args
     * @return HaploActiveRecord|bool
     */
    public static function __callStatic($name, $args) {
        if (substr($name, 0, strlen('find_by_')) === 'find_by_') {
            return static::find_by($name, $args);
        }
        return false;
    }

    /**
     * Alias to calling new Class($id) directly
     *
     * @param int $id
     * @return mixed
     */
    public static function find($id) {
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
    public static function find_by_sql($sql, $params = array(), $page = 0, $numPerPage = 0) {
        $params = static::format_bind_params($params);
        list($start, $count) = self::$db->get_offsets_from_page($page, $numPerPage);
        $results = self::$db->get_array($sql, $params, $start, $count, true);
        $paging = self::$db->get_paging($page, $numPerPage);
        $objects = array();
        foreach ($results as $result) {
            $objects[] = static::hydrate($result);
        }
        return $page !== 0 && $numPerPage !== 0 ? array($objects, $paging) : $objects;
    }

    public static function find_one_by_sql($sql, $params = array()) {
        $params = static::format_bind_params($params);
        return self::$db->get_row($sql, $params);
    }

    /**
     * @param string $name
     * @param array $args
     * @return bool|HaploActiveRecord
     */
    protected static function find_by($name, $args) {
        /** @var object $result */
        $result = self::$sqlBuilder->where(str_replace($name, 'find_by_', ''), '=', $args[0])
            ->get(static::table_name(), true);
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
    protected static function hydrate($result) {
        $primaryKey = static::primary_key();
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

    protected static function format_bind_params($params) {
        $processed = array();
        foreach ($params as $key => $value) {
            $processed[':'.$key] = $value;
        }
        return $processed;
    }
}