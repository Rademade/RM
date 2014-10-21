<?php
use RM_Cassandra_Query_WhereClause as QueryWhereClause;
use RM_Cassandra_Query_TableDecorator as TableDecorator;

/**
 * Class RM_Cassandra_Query_Select
 * @link http://www.datastax.com/documentation/cql/3.0/cql/cql_reference/select_r.html
 */
class RM_Cassandra_Query_Select
    implements
        RM_Cassandra_Query_Conditional {

    /**
     * @var string
     */
    protected $_table;

    /**
     * @var string|array
     */
    protected $_columns;

    /**
     * @var QueryWhereClause
     */
    protected $_where;

    /**
     * @var array
     */
    protected $_order;

    /**
     * @var int|string
     */
    protected $_limit;

    /**
     * @var bool
     */
    protected $_allowFiltering;

    public function __construct($table = null) {
        $this->from($table);
    }

    public function from($table) {
        $this->_table = $table;
        return $this;
    }

    public function columns($columns) {
        $this->_columns = $columns;
        return $this;
    }

    public function where($condition = null) {
        if (!$this->_where) {
            $this->_where = new QueryWhereClause();
        }
        if (null !== $condition) {
            $this->_where->raw($condition);
        }
        return $this->_where;
    }

    public function order($column, $order) {
        if (!$this->_order) {
            $this->_order = [];
        }
        $this->_order[] = $column . ' ' . $order;
        return $this;
    }

    public function limit($num) {
        $this->_limit = $num;
        return $this;
    }

    public function one() {
        return $this->limit(1);
    }

    public function allowFiltering() {
        $this->_allowFiltering = true;
        return $this;
    }

    public function disallowFiltering() {
        $this->_allowFiltering = false;
        return $this;
    }

    public function assemble() {
        $cql = 'SELECT ' . $this->__assembleColumns();
        $cql .= ' ' . $this->__assembleTable();
        if ($code = $this->__assembleWhere()) {
            $cql .= ' ' . $code;
        }
        if ($code = $this->__assembleOrder()) {
            $cql .= ' ' . $code;
        }
        if ($code = $this->__assembleDirectives()) {
            $cql .= ' ' . $code;
        }
        return $cql . ';';
    }

    public function __toString() {
        return $this->assemble();
    }

    protected function __assembleColumns() {
        if (is_string($this->_columns)) {
            return $this->_columns;
        }
        if (is_array($this->_columns)) {
            return join(', ', array_map(function($column) {
                return TableDecorator::getInstance()->decorateColumn($column);
            }, $this->_columns));
        }
        return $this->__defaultColumns();
    }

    protected function __assembleTable() {
        return 'FROM ' . TableDecorator::getInstance()->decorateName($this->_table);
    }

    protected function __assembleWhere() {
        if ($this->_where instanceof QueryWhereClause) {
            return $this->_where->assemble();
        }
        return $this->__defaultWhere();
    }

    protected function __assembleOrder() {
        if (!empty($this->_order)) {
            return join(' ', $this->_order);
        }
        return $this->__defaultOrder();
    }

    protected function __assembleDirectives() {
        $limit = (int)$this->_limit;
        $code = '';
        if ($limit > 0) {
            $code .= 'LIMIT ' . $limit;
        }
        if ($this->_allowFiltering) {
            $code .= ($code ? ' ' : '') . 'ALLOW FILTERING';
        }
        return $code;
    }

    protected function __defaultColumns() {
        return '*';
    }

    protected function __defaultWhere() {
        return '';
    }

    protected function __defaultOrder() {
        return '';
    }

}