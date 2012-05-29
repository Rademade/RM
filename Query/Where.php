<?php
class RM_Query_Where
	implements
		RM_Query_Interface_ImproveSelect,
		RM_Query_Interface_Hashable {

	/**
	 * @var array
	 */
	private $_conditions = array();

	const EXACTLY = 1;
	const MORE = 2;
	const LESS = 3;
	const NOT = 4;
	const IS = 5;

	const SQL_AND = 1;
	const SQL_OR = 2;

	private function _createConditionStdClass($field, $conditionType, $value, $type) {
		$condition = new stdClass();
		$condition->field = trim($field);
		$condition->condition = $this->_checkCondition( $conditionType );
		$condition->value = $value;
		$condition->type = $type;
		return $condition;
	}

	public function add($field, $conditionType, $value) {
		$this->_conditions[] = $this->_createConditionStdClass(
			$field,
			$conditionType,
			$value,
			self::SQL_AND
		);
		return $this;
	}

	public function addOr($field, $conditionType, $value) {
		$this->_conditions[] = $this->_createConditionStdClass(
			$field,
			$conditionType,
			$value,
			self::SQL_OR
		);
	}

	public function addSub(RM_Query_Where $subConditions) {
		$this->_conditions[] = $subConditions;
	}

	private function _checkCondition($type) {
		$type = (int)$type;
		if (in_array($type, array(
			self::EXACTLY,
			self::MORE,
			self::LESS,
		    self::NOT,
		    self::IS
		))) {
			return $type;
		} else {
			throw new Exception('WRONG CONDITION TYPE GIVEN');
		}
	}

	private function _convertCondition($type, $value) {
		if (is_array($value)) {
			if ($type === self::EXACTLY) {
				return 'IN';
			} else {
				throw new Exception('Array value given with not exactly type');
			}
		} else {
			switch ((int)$type) {
				case self::EXACTLY:
					return '=';
				case self::LESS:
					return '<';
				case self::MORE:
					return '>';
				case self::NOT:
					return '!=';
				case self::IS:
					return 'IS';
			}
		}
	}

	public function isHashable(){
		return true;
	}

	public function getHash() {
		return '_' . md5(
			$this->getConditionString()
		);
	}


    protected function _quote($value) {
        if (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }

    public function quote($value, $type = null) {
        if ($value instanceof Zend_Db_Select) {
	        /* @var $value Zend_Db_Select */
            return '(' . $value->assemble() . ')';
        }
        if ($value instanceof Zend_Db_Expr) {
            return $value->__toString();
        }
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val, $type);
            }
            return '(' . implode(', ', $value) . ')';
        }
	    if (is_null($value)) {
			return NULL;
	    }
        return $this->_quote($value);
    }

	private function _formatValue($value) {
		if (is_null($value)) {
			return 'NULL';
		} else {
			return $this->quote($value);
		}
	}

	private function _isEmptyArray($value) {
		return is_array($value) && empty($value);
	}

	private function _getConditionType($type) {
		switch ($type) {
			case self::SQL_AND:
				return 'AND';
			case self::SQL_OR:
				return 'OR';
		}
	}

	protected function _getFirstCondition(){
		if (!empty($this->_conditions)) {
			if (!($this->_conditions[0] instanceof self)) {
				return $this->_getConditionType($this->_conditions[0]->type);
			}
		}
		return '';
	}

	public function getConditionString() {
		$i = 0;
		$sql = '(';
		foreach ($this->_conditions as $condition) {
			++$i;
			if ($condition instanceof self) {
				/* @var $condition RM_Query_Where */
				if ($i !== 1) {
					$sql .= $condition->_getFirstCondition() . ' ';
				}
				$sql .= $condition->getConditionString();
			} else {
				/* @var $condition stdClass */
				if ($i !== 1) {
					$sql .= ($this->_getConditionType($condition->type) . ' ');
				}
				$value = $condition->value;
				if (!$this->_isEmptyArray($value)) {
					$sql .= join(' ', array(
						$condition->field,
						$this->_convertCondition( $condition->condition, $value ),
						$this->_formatValue($value)
					)) . ' ';
				}
			}
		}
		return $sql . ')';
	}

	public function improveQuery(Zend_Db_Select $select) {
		if (!empty($this->_conditions))
			$select->where( $this->getConditionString() );
	}

}