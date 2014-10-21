<?php
use RM_Cassandra_Query_ValueDecorator as QueryValueDecorator;

class RM_Cassandra_Query_WhereClause {

    const OP_EQ = '=';
    const OP_NOT_EQ = '!=';
    const OP_GR = '>';
    const OP_IN = 'IN';

    protected $_conditions;

    /**
     * @var string
     */
    protected $_column;

    /**
     * @var string
     */
    protected $_operation;

    /**
     * @var mixed
     */
    protected $_value;

    /**
     * @var int
     */
    protected $_treatedAs;

    /**
     * @param $column
     *
     * @return RM_Cassandra_Query_WhereClause
     */
    public function valueOf($column) {
        $this->__cleanup();
        $this->_column = $column;
        return $this;
    }

    public function equalsTo($value) {
        $this->_operation = self::OP_EQ;
        $this->_value = $value;
        return $this;
    }

    public function greaterThan($value) {
        $this->_operation = self::OP_GR;
        $this->_value = $value;
        return $this;
    }

    public function asInteger() {
        return $this->treatedAs(QueryValueDecorator::AS_INTEGER);
    }

    public function asString() {
        return $this->treatedAs(QueryValueDecorator::AS_STRING);
    }

    public function asUuid() {
        return $this->treatedAs(QueryValueDecorator::AS_UUID);
    }

    public function raw($condition) {
        if (!$this->_conditions) {
            $this->_conditions = [];
        }
        $this->_conditions[] = $condition;
        return $this;
    }

    public function assemble() {
        $this->__cleanup();
        if (empty($this->_conditions)) {
            return '';
        }
        $code = 'WHERE ';
        foreach ($this->_conditions as $i => $cnd) {
            if ($i > 0) $code .= ' AND ';
            if (is_string($cnd)) {
                $code .= $cnd;
            } else {
                $op = $this->__getOperation($cnd[2], $cnd[1]);
                $code .= $this->__tableDecorator()->decorateColumn($cnd[0]);
                $code .= ' ' . $op . ' ';
                $code .= $this->__valueDecorator()->decorate($cnd[2], $cnd[3]);
            }
        }
        return $code;
    }

    public function __toString() {
        return $this->assemble();
    }

    public function treatedAs($tp) {
        $this->_treatedAs = $tp;
        return $this;
    }

    protected function __cleanup() {
        $c = &$this->_column;
        $o = &$this->_operation;
        $v = &$this->_value;
        $t = &$this->_treatedAs;
        if (null !== $c && null !== $o && null !== $v) {
            $this->raw([$c, $o, $v, $t]);
        }
        $c = $o = $v = $t = null;
    }

    protected function __tableDecorator() {
        return RM_Cassandra_Query_TableDecorator::getInstance();
    }

    protected function __valueDecorator() {
        return QueryValueDecorator::getInstance();
    }

    protected function __getOperation($value, $op) {
        if (is_array($value)) {
            return self::OP_IN;
        }
        return self::OP_IN === $op ? self::OP_EQ : $op;
    }

}