<?php
use RM_Cassandra_Query_WhereClause as QueryWhereClause;
use RM_Cassandra_Query_TableDecorator as TableDecorator;

/**
 * Class RM_Cassandra_Query_Delete
 * @link http://www.datastax.com/documentation/cql/3.0/cql/cql_reference/delete_r.html
 */
class RM_Cassandra_Query_Delete
    implements
        RM_Cassandra_Query_Conditional {

    /**
     * @var string
     */
    protected $_table;

    /**
     * @var QueryWhereClause
     */
    protected $_where;

    public function __construct($table = null) {
        $this->from($table);
    }

    public function from($table) {
        $this->_table = $table;
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

    public function assemble() {
        $cql = 'DELETE ' . $this->__assembleTable();
        if ($code = $this->__assembleWhere()) {
            $cql .= ' ' . $code;
        }
        return $cql . ';';
    }

    public function __toString() {
        return $this->assemble();
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

    protected function __defaultWhere() {
        return '';
    }

}