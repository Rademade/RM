<?php
class RM_Query_Where
    extends
        RM_Query_Where_Abstract {

    /**
     * @var RM_Query_Where_Glue[]
     */
    protected $_conditions = array();

    /**
     * @param $field
     * @param $type
     * @param $value
     * @return RM_Query_Where
     */
    public function add($field, $type, $value) {
        $condition = new RM_Query_Where_Condition($field, $type, $value);
        $this->_conditions[] =  new RM_Query_Where_Glue($condition, RM_Query_Where_Glue::SQL_AND);
        return $this;
    }

    /**
     * @param $field
     * @param $type
     * @param $value
     * @return RM_Query_Where
     */
    public function addOr($field, $type, $value) {
        $condition = new RM_Query_Where_Condition($field, $type, $value);
        $this->_conditions[] =  new RM_Query_Where_Glue($condition, RM_Query_Where_Glue::SQL_OR);
        return $this;
    }
    
    /**
     * @param RM_Query_Where $subCondition
     * @return RM_Query_Where
     */
    public function addSub(self $subCondition) {
        $this->_conditions[] =  new RM_Query_Where_Glue($subCondition, RM_Query_Where_Glue::SQL_AND);
        return $this;
    }
    
    /**
     * @param RM_Query_Where $subCondition
     * @return RM_Query_Where
     */
    public function addSubOr(self $subCondition) {
        $this->_conditions[] =  new RM_Query_Where_Glue($subCondition, RM_Query_Where_Glue::SQL_OR);
        return $this;
    }
    
    public function mergeWith(self $subCondition) {
        foreach ($subCondition->_conditions as $condition) {
            $this->_conditions[] = $condition;
        }
    }
    
    public function getHash() {
        return '_' . md5( $this->_getConditionSQL() );
    }
    
    public function improveQuery(Zend_Db_Select $select) {
        if (!empty($this->_conditions)) {
            $select->where( $this->_getConditionSQL() );
        }
    }
    
    /**
     * @return string
     */
    protected function _getConditionSQL() {
        $i = 0;
        $sqlParts = array();
        foreach ($this->_conditions as $condition) {
            $conditionSQL = $condition->getCondition()->_getConditionSQL();
            if (!empty($conditionSQL)) {
                ++$i;
                if ($i !== 1) {
                    $sqlParts[] = $condition->getGlueTypeSQL();
                }
                $sqlParts[] =  $conditionSQL;
            }
        }
        return '(' . join(' ', $sqlParts) . ')';
    }

}