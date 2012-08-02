<?php
class RM_Query_Where
    extends
        RM_Query_Where_Abstract {

	/**
	 * @var RM_Query_Where_Glue[]
	 */
	protected $_conditions = array();

	public function add($field, $type, $value) {
        $condition = new RM_Query_Where_Condition($field, $type, $value);
		$this->_conditions[] =  new RM_Query_Where_Glue($condition, RM_Query_Where_Glue::SQL_AND);
		return $this;
	}

	public function addOr($field, $type, $value) {
        $condition = new RM_Query_Where_Condition($field, $type, $value);
        $this->_conditions[] =  new RM_Query_Where_Glue($condition, RM_Query_Where_Glue::SQL_OR);
        return $this;
	}

	public function addSub(self $subCondition) {
        $this->_conditions[] =  new RM_Query_Where_Glue($subCondition, RM_Query_Where_Glue::SQL_AND);
        return $this;
	}

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

	protected function _getConditionSQL() {
		$i = 0;
        $sqlParts = array();
		foreach ($this->_conditions as $condition) {
			++$i;
            if ($i !== 1) {
                $sqlParts[] = $condition->getGlueTypeSQL();
            }
            $sqlParts[] =  $condition->getCondition()->_getConditionSQL();
		}
		return '(' . join(' ', $sqlParts) . ')';
	}

}