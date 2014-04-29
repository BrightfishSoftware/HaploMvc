<?php
namespace HaploMvc\Db;

use HaploMvc\Exception\HaploInvalidComparisonOperatorException;
use HaploMvc\Exception\HaploInvalidSortOrderException;
use HaploMvc\Exception\HaploInvalidJoinTypeException;
use HaploMvc\Exception\HaploInvalidParameterException;

/**
 * Class HaploSqlBuilder
 * @package HaploMvc
 */
class HaploSqlBuilder
{
    /** @var HaploDb */
    protected $db;
    /** @var string */
    protected $select = '';
    /** @var string */
    protected $distinct = '';
    /** @var string */
    protected $from = '';
    /** @var string */
    protected $where = '';
    /** @var string */
    protected $having = '';
    /** @var string */
    protected $groupBy = '';
    /** @var string */
    protected $on = '';
    /** @var string */
    protected $join = '';
    /** @var string */
    protected $orderBy = '';
    /** @var string */
    protected $limit = '';
    /** @var bool */
    protected $single = false;
    /** @var array */
    protected $data = array();
    /** @var array */
    protected $comparisonOperators = array(
        '=',
        '<>',
        '!=',
        '<',
        '>',
        '>=',
        '<=',
        '<=>',
        'IS NULL',
        'IS NOT NULL',
        'IS',
        'IS NOT',
        'LIKE',
        'NOT LIKE',
        'IN',
        'NOT IN'
    );
    /** @var array */
    protected $joinTypes = array(
        'JOIN',
        'LEFT JOIN',
        'RIGHT JOIN',
        'OUTER JOIN',
        'INNER JOIN',
        'LEFT OUTER JOIN',
        'RIGHT OUTER JOIN'
    );
    /** @var bool */
    protected $isSubWhere = false;

    /**
     * @param HaploDb $db
     */
    public function __construct(HaploDb $db)
    {
        $this->db = $db;
    }

    /**
     * Usage:
     * $builder->select('title')
     * $builder->select(array('title', 'body'))
     * $builder->select('title', true) // no escaping
     *
     * @param array|string $fields
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function select($fields, $dontQuote = false)
    {
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
                $this->select .= $this->select === '' ? $field : ', '.$field;
            }
        } else {
            $field = $dontQuote ? $fields : $this->db->quoteIdentifier($fields);
            $this->select .= $this->select === '' ? $field : ', '.$field;
        }
        return $this;
    }

    /**
     * @param string $table
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function from($table, $dontQuote = false)
    {
        $table = $dontQuote ? $table : $this->db->quoteIdentifier($table);
        $this->from .= $this->from === '' ? $table : ', '.$table;
        return $this;
    }

    /**
     * _where($andOr, $callable);
     * _where($andOr, $field, $comparison, $value, $dontQuote = false)
     *
     * @throws \HaploMvc\Exception\HaploInvalidComparisonOperatorException
     * @throws \HaploMvc\Exception\HaploInvalidParameterException
     * @return HaploSqlBuilder $this
     */
    protected function _where()
    {
        $args = func_get_args();

        if (count($args) === 0) {
            throw new HaploInvalidParameterException('Invalid parameters specified.');
        }

        $andOr = $args[0];

        if (isset($args[1]) && is_callable($args[1])) {
            $this->isSubWhere = true;
            $this->where .= $this->where === '' ? '(' : sprintf(' %s (', $andOr);
            $args[1]($this);
            $this->where .= ')';
            $this->isSubWhere = false;
            return $this;
        }

        $field = isset($args[1]) ? $args[1] : '';
        $comparison = isset($args[2]) ? $args[2] : '';
        $value = isset($args[3]) ? $args[3] : '';
        $dontQuote = isset($args[4]) ? (bool)$args[4] : false;

        if ($field === '' || $comparison === '' || $value === '') {
            throw new HaploInvalidParameterException('Invalid parameters specified.');
        }

        if (!in_array($comparison, $this->comparisonOperators)) {
            throw new HaploInvalidComparisonOperatorException('Invalid comparison operator specified.');
        }

        if (!is_array($value) || !empty($value)) {
            if (is_array($value)) {
                foreach ($value as &$current) {
                    $current = $dontQuote ? $current : $this->db->quote($current);
                }
                $value = '('.implode(',', $value).')';
            } else {
                $value = $dontQuote ? $value : $this->db->quote($value);
            }
            $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
            $sql = sprintf('%s %s %s', $field, $comparison, $value);
            if ($this->where === '' || $this->isSubWhere) {
                $this->where .= $sql;
                $this->isSubWhere = false;
            } else {
                $this->where .= sprintf(' %s %s', $andOr, $sql);
            }
        }
        return $this;
    }

    /**
     * where($callable);
     * where($field, $comparison, $value, $dontQuote = false)
     *
     * @return HaploSqlBuilder $this
     */
    public function where()
    {
        $args = array_merge(array('AND'), func_get_args());
        return call_user_func_array(array($this, '_where'), $args);
    }

    /**
     * where($callable);
     * where($field, $comparison, $value, $dontQuote = false)
     *
     * @return HaploSqlBuilder $this
     */
    public function orWhere()
    {
        $args = array_merge(array('OR'), func_get_args());
        return call_user_func_array(array($this, '_where'), $args);
    }

    /**
     * @param string $andOr
     * @param string $inNotIn
     * @param string $field
     * @param array $values
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    protected function _whereIn($andOr, $inNotIn, $field, array $values, $dontQuote)
    {
        if (!empty($values)) {
            foreach ($values as &$value) {
                $value = $dontQuote ? $value : $this->db->quote($value);
            }
            $values = implode(',', $values);
            $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
            $sql = sprintf('%s %s (%s)', $field, $inNotIn, $values);
            $this->where .= $this->where === '' ? $sql : sprintf(' %s %s', $andOr, $sql);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param array $values
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function whereIn($field, array $values, $dontQuote = false)
    {
        return $this->_whereIn('AND', 'IN', $field, $values, $dontQuote);
    }

    /**
     * @param string $field
     * @param array $values
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function whereNotIn($field, array $values, $dontQuote = false)
    {
        return $this->_whereIn('AND', 'NOT IN', $field, $values, $dontQuote);
    }

    /**
     * @param string $field
     * @param array $values
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function orWhereIn($field, array $values, $dontQuote = false)
    {
        return $this->_whereIn('OR', 'IN', $field, $values, $dontQuote);
    }

    /**
     * @param string $field
     * @param array $values
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function orWhereNotIn($field, array $values, $dontQuote = false)
    {
        return $this->_whereIn('OR', 'NOT IN', $field, $values, $dontQuote);
    }

    /**
     * @param string $andOr
     * @param string $likeNotLike
     * @param string $field
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    protected function _whereLike($andOr, $likeNotLike, $field, $value, $dontQuote)
    {
        $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
        $value = $dontQuote ? $value : $this->db->quote($value);
        $sql = sprintf('%s %s %s', $field, $likeNotLike, $value);
        $this->where .= $this->where === '' ? $sql : sprintf(' %s %s', $andOr, $sql);
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function whereLike($field, $value, $dontQuote = false)
    {
        return $this->_whereLike('AND', 'LIKE', $field, $value, $dontQuote);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function whereNotLike($field, $value, $dontQuote = false)
    {
        return $this->_whereLike('AND', 'NOT LIKE', $field, $value, $dontQuote);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function orWhereLike($field, $value, $dontQuote = false)
    {
        return $this->_whereLike('OR', 'LIKE', $field, $value, $dontQuote);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function orWhereNotLike($field, $value, $dontQuote = false)
    {
        return $this->_whereLike('OR', 'NOT LIKE', $field, $value, $dontQuote);
    }

    /**
     * @param string $field
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function groupBy($field, $dontQuote = false)
    {
        $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
        $this->groupBy .= $this->groupBy === '' ? $field : ', '.$field;
        return $this;
    }

    /**
     * @return HaploSqlBuilder $this
     */
    public function distinct()
    {
        $this->distinct = 'DISTINCT ';
        return $this;
    }

    /**
     * @param string $andOr
     * @param string $field
     * @param string $comparison
     * @param mixed $value
     * @param bool $dontQuote
     * @throws \HaploMvc\Exception\HaploInvalidComparisonOperatorException
     * @return HaploSqlBuilder $this
     */
    protected function _having($andOr, $field, $comparison, $value, $dontQuote)
    {
        if (!in_array($comparison, $this->comparisonOperators)) {
            throw new HaploInvalidComparisonOperatorException('Invalid comparison operator specified.');
        }
        if (!is_array($value) || !empty($value)) {
            if (is_array($value)) {
                foreach ($value as &$current) {
                    $current = $dontQuote ? $current : $this->db->quote($current);
                }
                $value = '('.implode(',', $value).')';
            } else {
                $value = $dontQuote ? $value : $this->db->quote($value);
            }
            $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
            $sql = sprintf('%s %s %s', $field, $comparison, $value);
            $this->having .= $this->having === '' ? $sql : sprintf(' %s %s', $andOr, $sql);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param string $comparison
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function having($field, $comparison, $value, $dontQuote = false)
    {
        return $this->_having('AND', $field, $comparison, $value, $dontQuote);
    }

    /**
     * @param string $field
     * @param string $comparison
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function orHaving($field, $comparison, $value, $dontQuote = false)
    {
        return $this->_having('OR', $field, $comparison, $value, $dontQuote);
    }

    /**
     * @param string $field
     * @param string $order
     * @return HaploSqlBuilder $this
     * @throws \HaploMvc\Exception\HaploInvalidSortOrderException
     */
    public function orderBy($field, $order = 'ASC')
    {
        if (!in_array($order, array('ASC', 'DESC'))) {
            throw new HaploInvalidSortOrderException('Invalid sort order specified.');
        }
        $sql = $this->db->quoteIdentifier($field).' '.$order;
        $this->orderBy .= $this->orderBy === '' ? $sql : ', '.$sql;
        return $this;
    }

    /**
     * @return HaploSqlBuilder $this
     */
    public function limit()
    {
        $args = func_get_args();
        if (count($args) == 2) {
            $this->limit = sprintf(' LIMIT %d, %d', (int)$args[0], (int)$args[1]);
            $this->single = (int)$args[1] === 1;
        } else {
            $this->limit = ' LIMIT '.(int)$args[0];
            $this->single = (int)$args[0] === 1;
        }
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param bool $dontQuote
     * @return HaploSqlBuilder $this
     */
    public function set($field, $value, $dontQuote = false)
    {
        $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
        $value = $dontQuote ? $value : $this->db->quote($value);
        $this->data[$field] = $value;
        return $this;
    }

    protected function _on($andOr, $field, $comparison, $value, $dontQuote)
    {
        if (!in_array($comparison, $this->comparisonOperators)) {
            throw new HaploInvalidComparisonOperatorException('Invalid comparison operator specified.');
        }
        $field = $dontQuote ? $field : $this->db->quoteIdentifier($field);
        $value = $dontQuote ? $value : $this->db->quoteIdentifier($value);
        $sql = sprintf('%s %s %s', $field, $comparison, $value);
        $this->on = $this->on === '' ? $sql : sprintf(' %s %s', $andOr, $sql);
        return $this;
    }

    public function on($field, $comparison, $value, $dontQuote = false)
    {
        return $this->_on('AND', $field, $comparison, $value, $dontQuote);
    }

    public function orOn($field, $comparison, $value, $dontQuote = false)
    {
        return $this->_on('OR', $field, $comparison, $value, $dontQuote);
    }

    /**
     * @param string $table
     * @param string $type
     * @throws HaploInvalidJoinTypeException
     * @return HaploSqlBuilder $this
     */
    protected function _join($table, $type = 'JOIN')
    {
        $type = strtoupper($type);
        if (!in_array($type, $this->joinTypes)) {
            throw new HaploInvalidJoinTypeException('Invalid join type specified.');
        }
        $sql = sprintf('%s %s ON %s', $type, $table, $this->on);
        $this->join .= $this->join === '' ? $sql : ', '.$sql;
        $this->on = '';
        return $this;
    }

    /**
     * @param string $table
     * @return HaploSqlBuilder $this
     */
    public function join($table)
    {
        return $this->_join($table, 'JOIN');
    }

    /**
     * @param string $table
     * @return HaploSqlBuilder $this
     */
    public function leftJoin($table)
    {
        return $this->_join($table, 'LEFT JOIN');
    }

    /**
     * @param string $table
     * @return HaploSqlBuilder $this
     */
    public function rightJoin($table)
    {
        return $this->_join($table, 'RIGHT JOIN');
    }

    /**
     * @param string $table
     * @return HaploSqlBuilder $this
     */
    public function outerJoin($table)
    {
        return $this->_join($table, 'OUTER JOIN');
    }

    /**
     * @param string $table
     * @return HaploSqlBuilder $this
     */
    public function innerJoin($table)
    {
        return $this->_join($table, 'INNER JOIN');
    }

    /**
     * @param string $table
     * @return HaploSqlBuilder $this
     */
    public function leftOuterJoin($table)
    {
        return $this->_join($table, 'LEFT OUTER JOIN');
    }

    /**
     * @param string $table
     * @return HaploSqlBuilder $this
     */
    public function rightOuterJoin($table)
    {
        return $this->_join($table, 'RIGHT OUTER JOIN');
    }

    /**
     * @param string $table
     * @param array $data
     * @return int|bool
     */
    public function insert($table = '', array $data = array())
    {
        if ($table === '') {
            $table = $this->from;
        }
        $sql = 'INSERT INTO '.$this->db->quoteIdentifier($table);
        if (!empty($data)) {
            $names = array_keys($data);
            $values = array_values($data);
            foreach ($names as &$name) {
                $name = $this->db->quoteIdentifier($name);
            }
            foreach ($values as &$value) {
                $value = $this->db->quote($value);
            }
        } else {
            $names = array_keys($this->data);
            $values = array_values($this->data);
        }
        $names = implode(',', $names);
        $values = implode(',', $values);
        $sql .= sprintf(' (%s) VALUES (%s);', $names, $values);
        $this->reset();
        return $sql;
    }

    /**
     * @param string $table
     * @param array $data
     * @return bool
     */
    public function update($table = '', array $data = array())
    {
        $sql = sprintf('UPDATE %s SET ', $table !== '' ? $this->db->quoteIdentifier($table) : $this->from);
        if (!empty($data)) {
            foreach ($data as $name => $value) {
                $sql .= sprintf('%s = %s,', $this->db->quoteIdentifier($name), $this->db->quote($value));
            }
        } else {
            foreach ($this->data as $name => $value) {
                $sql .= sprintf('%s = %s,', $name, $value);
            }
        }
        $sql = rtrim($sql, ',');
        if ($this->where !== '') {
            $sql .= ' WHERE '.$this->where;
        }
        if ($this->orderBy !== '') {
            $sql .= ' ORDER BY '.$this->orderBy;
        }
        if ($this->limit !== '') {
            $sql .= $this->limit;
        }
        $sql .= ';';
        $this->reset();
        return $sql;
    }

    /**
     * @param string $table
     * @return bool
     */
    public function delete($table = '')
    {
        $sql = sprintf('DELETE FROM %s;', $table !== '' ? $this->db->quoteIdentifier($table) : $this->from);
        if ($this->where !== '') {
            $sql .= ' WHERE '.$this->where;
        }
        if ($this->orderBy !== '') {
            $sql .= ' ORDER BY '.$this->orderBy;
        }
        if ($this->limit !== '') {
            $sql .= $this->limit;
        }
        $sql .= ';';
        $this->reset();
        return $sql;
    }

    /**
     * @param string $table
     * @return bool
     */
    public function deleteAll($table = '')
    {
        return sprintf('DELETE FROM %s;', $table !== '' ? $this->db->quoteIdentifier($table) : $this->from);
    }

    /**
     * @param string $table
     * @return bool
     */
    public function truncate($table)
    {
        return sprintf('TRUNCATE %s;', $table !== '' ? $this->db->quoteIdentifier($table) : $this->from);
    }

    /**
     * @param string $table
     * @return array|bool
     */
    public function get($table = '')
    {
        $sql = sprintf(
            'SELECT %s%s FROM %s',
            $this->distinct,
            $this->select !== '' ? $this->select : '*',
            $table !== '' ? $this->db->quoteIdentifier($table) : $this->from
        );
        if ($this->where !== '') {
            $sql .= ' WHERE '.$this->where;
        }
        if ($this->groupBy !== '') {
            $sql .= ' GROUP BY '.$this->groupBy;
        }
        if ($this->having !== '') {
            $sql .= ' HAVING '.$this->having;
        }
        if ($this->orderBy !== '') {
            $sql .= ' ORDER BY '.$this->orderBy;
        }
        if ($this->limit !== '') {
            $sql .= $this->limit;
        }
        $sql .= ';';
        $this->reset();
        return $sql;
    }

    /**
     * @param string $table
     * @return int
     */
    public function count($table = '')
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s', $table !== '' ? $this->db->quoteIdentifier($table) : $this->from);
        if ($this->where !== '') {
            $sql .= ' WHERE '.$this->where;
        }
        if ($this->groupBy !== '') {
            $sql .= ' GROUP BY '.$this->groupBy;
        }
        if ($this->having !== '') {
            $sql .= ' HAVING '.$this->having;
        }
        if ($this->orderBy !== '') {
            $sql .= ' ORDER BY '.$this->orderBy;
        }
        $sql .= ';';
        $this->reset();
        return $sql;
    }

    public function reset()
    {
        $this->select = '';
        $this->distinct = '';
        $this->from = '';
        $this->where = '';
        $this->having = '';
        $this->groupBy = '';
        $this->join = '';
        $this->orderBy = '';
        $this->limit = '';
        $this->single = false;
        $this->data = array();
    }
}
