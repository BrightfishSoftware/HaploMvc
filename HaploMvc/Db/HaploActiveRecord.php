<?php
namespace HaploMvc\Db;

use \Exception;

/**
 * Class HaploActiveRecord
 * @package HaploMvc\Db
 */
abstract class HaploActiveRecord {
    /** @var HaploQueryBuilder */
    protected static $builder = null;
    /** @var array */
    protected $fields = null;
    /** @var int */
    public $id = null;

    /**
     * @param HaploQueryBuilder $builder
     */
    public static function set_db(HaploQueryBuilder $builder) {
        static::$builder = $builder;
    }

    /**
     * @return mixed
     */
    abstract public function table_name();

    /**
     * @return string
     */
    public function primary_key() {
        return $this->table_name().'_id';
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
     * @throws \Exception
     */
    public function __set($name, $value) {
        if (!is_null($this->id) && !array_key_exists($name, $this->fields)) {
            throw new Exception(sprintf('Column %s does not exist in table %s.', $name, $this->table_name()));
        }
        $this->fields[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name) {
        if ($name === $this->primary_key()) {
            return $this->id;
        }
        if (!is_null($this->id) && !array_key_exists($name, $this->fields)) {
            throw new Exception(sprintf('Column %s does not exist in table %s.', $name, $this->table_name()));
        }
        return array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
    }

    /**
     * @param int $id
     * @throws \Exception
     */
    protected function load($id) {
        $this->id = $id;
        $this->fields = static::$builder->where($this->primary_key(), '=', $this->id)
            ->get($this->table_name());
        if (empty($this->fields)) {
            throw new Exception(sprintf('ID %d not found in %.', $this->id, $this->table_name()));
        }
        unset($this->fields[$this->primary_key()]);
    }

    /**
     * @return bool|int
     */
    public function save() {
        if (!is_null($this->id)) { // update
            static::$builder->where($this->primary_key(), '=', $this->id);
            return static::$builder->update($this->table_name(), $this->fields);
        } else { // insert
            return static::$builder->insert($this->table_name(), $this->fields);
        }
    }

    /**
     * @return bool
     */
    public function delete() {
        if (is_null($this->id)) {
            return false;
        }
        static::$builder->where($this->primary_key(), '=', $this->id)
            ->delete($this->table_name());
        $this->id = null;
    }

    /**
     * @param string $name
     * @param array $args
     * @return HaploActiveRecord
     */
    public static function __callStatic($name, $args) {
        if (substr($name, 0, strlen('find_by_')) === 'find_by_') {
            $class = get_called_class();
            /** @var HaploActiveRecord $base */
            $base = new $class;
            /** @var object $resultObject */
            $resultObject = static::$builder->where(str_replace($name, 'find_by_', ''), '=', $args[0])
                ->get($base->table_name(), true);
            $primaryKey = $base->primary_key();
            foreach (get_object_vars($resultObject) as $key => $value) {
                if ($key !== $primaryKey) {
                    $base->$key = $value;
                }
            }
            $base->id = $resultObject->$primaryKey;
            return $base;
        }
    }
}