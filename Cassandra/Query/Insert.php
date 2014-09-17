<?php
use RM_Cassandra_Query_ValueDecorator as QueryValueDecorator;
use RM_Cassandra_Query_TableDecorator as TableDecorator;

/**
 * Class RM_Cassandra_Query_Insert
 * @link http://www.datastax.com/documentation/cql/3.0/cql/cql_reference/insert_r.html
 */
class RM_Cassandra_Query_Insert {

    /**
     * @var string
     */
    protected $_table;

    /**
     * @var array
     */
    protected $_columns;

    /**
     * @var array
     */
    protected $_values;

    /**
     * @var array
     */
    protected $_treatedAs;

    protected $_value;
    protected $_column;
    protected $_tp;

    public function __construct($table = null) {
        $this->into($table);
    }

    public function into($table) {
        $this->_table = $table;
        return $this;
    }

    public function value($value) {
        $this->__cleanup();
        $this->_value = $value;
        return $this;
    }

    public function namedAs($column) {
        $this->_column = $column;
        return $this;
    }

    public function treatedAs($tp) {
        $this->_tp = $tp;
        return $this;
    }

    public function assemble() {
        $this->__cleanup();
        $cql = 'INSERT INTO ';
        $cql .= $this->__tableDecorator()->decorateName($this->_table);
        $cql .= ' (' . $this->__assembleColumns() . ')';
        return $cql . ' VALUES(' . $this->__assembleValues() . ');';
    }

    public function __toString() {
        return $this->assemble();
    }

    protected function __cleanup() {
        $v = &$this->_value;
        $c = &$this->_column;
        $t = &$this->_tp;
        if (null !== $v && null !== $c) {
            if (!$this->_columns) {
                $this->_columns = [];
                $this->_values = [];
                $this->_treatedAs = [];
            }
            $this->_columns[] = $c;
            $this->_values[] = $v;
            $this->_treatedAs[] = $t;
        }
        $v = $c = $t = null;
    }

    protected function __assembleValues() {
        $code = '';
        $i = 0;
        $length = sizeof($this->_values);
        $decorator = $this->__valueDecorator();
        while ($i < $length) {
            $code .= $decorator->decorate($this->_values[$i], $this->_treatedAs[$i]);
            if (++$i < $length) $code .= ',';
        }
        return $code;
    }

    protected function __assembleColumns() {
        $code = '';
        $i = 0;
        $length = sizeof($this->_columns);
        $decorator = $this->__tableDecorator();
        while ($i < $length) {
            $code .= $decorator->decorateColumn($this->_columns[$i]);
            if (++$i < $length) $code .= ',';
        }
        return $code;
    }

    protected function __tableDecorator() {
        return TableDecorator::getInstance();
    }

    protected function __valueDecorator() {
        return QueryValueDecorator::getInstance();
    }

}