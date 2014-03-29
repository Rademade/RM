<?php
abstract class RM_Query_Where_Abstract
    implements
        RM_Query_Interface_ImproveSelect,
        RM_Query_Interface_Hashable {

    const EXACTLY = 1;//IN, =
    const NOT_IN = 11;
    const MORE = 2;
    const LESS = 3;
    const NOT = 4;
    const IS = 5;
    const LIKE = 6; //LIKE %val%
    const START_LIKE = 7; //LIKE val%
    const END_LIKE = 8; //LIKE %val
    const MORE_EXACTLY = 9;
    const LESS_EXACTLY = 10;
    //const NOT_IN = 11;
    const FULLTEXT_MATCH = 12;

    public function isHashable(){
        return true;
    }

    public function getHash() {
        return '_' . md5( $this->_getConditionSQL() );
    }

    public function improveQuery(Zend_Db_Select $select) {
        if (!empty($select)) {
            $select->where($this->_getConditionSQL());
        }
    }

    abstract protected function _getConditionSQL();

    protected function __quoteVal($value) {
        if (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }

    protected function __quote($value, $type = null) {
        if ($value instanceof Zend_Db_Select) {
            /* @var $value Zend_Db_Select */
            return '(' . $value->assemble() . ')';
        }
        if ($value instanceof Zend_Db_Expr) {
            /* @var $value Zend_Db_Expr */
            return $value->__toString();
        }
        if (is_array($value)) {
            /* @var $value array */
            foreach ($value as &$val) {
                $val = $this->__quote($val, $type);
            }
            return '(' . join(', ', $value) . ')';
        }
        if (is_null($value)) {
            return NULL;
        }
        return $this->__quoteVal($value);
    }

}