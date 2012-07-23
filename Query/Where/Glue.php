<?php
class RM_Query_Where_Glue {

    private $_condition;
    private $_glueType;

    const SQL_AND = 1;
    const SQL_OR = 2;

    public function __construct(RM_Query_Where_Abstract $condition, $type) {
        $this->_condition = $condition;
        $this->_glueType = $this->_checkGlueType($type);
    }

    public function getCondition() {
        return $this->_condition;
    }

    public function getGlueTypeSQL() {
        switch ( $this->getGlueType() ) {
            case self::SQL_AND:
                return 'AND';
            case self::SQL_OR:
                return 'OR';
        }
    }

    public function getGlueType() {
        return $this->_glueType;
    }

    private function _checkGlueType($type) {
        $type = (int)$type;
        if (!in_array($type, array(
            self::SQL_AND,
            self::SQL_OR
        ))) {
            throw new Exception('Wrong glue type given');
        }
        return $type;
    }

}
