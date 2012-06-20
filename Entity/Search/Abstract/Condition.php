<?php
abstract class RM_Entity_Search_Abstract_Condition
    extends
        RM_Entity_Search_Abstract_Rules {

    /**
     * @var RM_Entity_Search_Condition[]
     */
    private $_searchConditions;

    /**
     * @var string
     */
    private $_searchConditionType;

    /**
     * @param string $type
     */
    public function setConditionType($type) {
        $this->_searchConditionType = trim( (string)$type );
    }

    /**
     * @return string
     */
    public function getConditionType() {
        return $this->_searchConditionType;
    }

    /**
     * @return RM_Entity_Search_Condition[]
     */
    public function getConditions() {
        return $this->_searchConditions;
    }

    /**
     * @param RM_Entity_Search_Condition[] $searchConditions
     */
    public function setCondition(array $searchConditions) {
        $this->_searchConditions = $searchConditions;
    }

    /**
     * @return RM_Entity_Search_Condition[]
     */
    protected final function _getMatchedConditions() {
        $conditions = array();
        foreach ($this->getConditions() as $condition) {
            $condition->setPhrase( $this->getPhrase() );
            if ($condition->isMatch() && $this->_typeIsMatch( $condition )) {
                $condition->__copyFrom( $this );
                $conditions[] = $condition;
            }
        }
        return $conditions;
    }

    /**
     * @param Zend_Db_Select $select
     */
    protected function __setConditionToQuery(Zend_Db_Select $select) {
        $where = new RM_Query_Where();
        foreach ($this->_getMatchedConditions() as $condition) {
            $condition->setSearchJoins( $select );
            $condition->setSearchConditions( $where );
        }
        $where->improveQuery( $select );
    }

    /**
     * @param RM_Entity_Search_Abstract_Condition $search
     * @throws Exception
     */
    public function __copyFrom($search) {
        if ($search instanceof RM_Entity_Search_Abstract_Condition) {
            parent::__copyFrom( $search );
            $this->setConditionType( $search->getConditionType() );
            $this->setCondition( $search->getConditions() );
        } else {
            throw new Exception('$search must be instance of RM_Entity_Search_Abstract_Condition');
        }
    }

    /**
     * @param RM_Entity_Search_Condition $condition
     * @return bool
     */
    private function _typeIsMatch(RM_Entity_Search_Condition $condition) {
        return $this->getConditionType() === '' || $this->getConditionType() === $condition->getConditionType();
    }

}