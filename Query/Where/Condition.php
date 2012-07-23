<?php
class RM_Query_Where_Condition
    extends
        RM_Query_Where_Abstract {

    private $_fieldName;

    private $_conditionType;

    private $_value;


    public function __construct(
        $fieldName,
        $conditionType,
        $value
    ) {
        $this->_fieldName = $fieldName;
        $this->_conditionType = $this->_convertCondition( $conditionType );
        $this->_value = $value;
    }

    public function getField() {
        return $this->_fieldName;
    }

    public function getValue() {
        return $this->_value;
    }

    public function getInitConditionType() {
        return $this->_conditionType;
    }

    public function getConditionType() {
        if (is_array( $this->getValue() )) {
            if ($this->getInitConditionType() === self::EXACTLY) return 'IN';
            throw new Exception('Array value given with not exactly type');
        } else {
            switch ( $this->getInitConditionType() ) {
                case    self::EXACTLY:      return '=';
                case    self::LESS:         return '<';
                case    self::MORE:         return '>';
                case    self::NOT:          return '!=';
                case    self::IS:           return 'IS';
                case    self::LIKE:
                case    self::START_LIKE:
                case    self::END_LIKE:     return 'LIKE';
            }
        }
    }

    protected function _getConditionSQL() {
        $sql = '';
        if (!$this->_isEmptyArray()) {
            $sql =  join(' ', array(
                $this->getField(),
                $this->getConditionType(),
                $this->_getSQLValue()
            ));
        }
        return $sql;
    }

    private function _getSQLValue() {
        if (is_null( $this->getValue() )) {
            $value = 'NULL';
        } else {
            $value = $this->getValue();
            if ($this->_isLikeType()) {
                $value = $this->_wrapLikeValue( $value );
            }
            $value = $this->__quote( $value );
        }
        return $value;
    }

    private function _convertCondition($type) {
        switch ($type) {
            case '=':
            case self::EXACTLY:
            case 'IN':
                return self::EXACTLY;
            case '>':
            case self::MORE:
                return self::MORE;
            case '<':
            case self::LESS:
                return self::LESS;
            case self::NOT:
            case '!=':
                return self::NOT;
            case self::IS:
            case 'IS':
                return self::IS;
            case 'LIKE':
            case self::LIKE:
                return self::LIKE;
            case self::START_LIKE:
                return self::START_LIKE;
            case self::END_LIKE:
                return self::END_LIKE;
            default:
                throw new Exception('WRONG CONDITION TYPE GIVEN');
        }
    }

    private function _isEmptyArray() {
        return is_array( $this->getValue() ) && sizeof( $this->getValue() ) === 0;
    }

    private function _isLikeType() {
        return in_array($this->getInitConditionType(), array(
            self::LIKE,
            self::START_LIKE,
            self::END_LIKE
        ));
    }

    private function _wrapLikeValue($value) {
        switch ( $this->getInitConditionType() ) {
            case self::LIKE:
                return '%' . $value . '%';
            case self::START_LIKE:
                return $value . '%';
            case self::END_LIKE:
                return '%' . $value;
            default:
                return $value;
        }
    }

}