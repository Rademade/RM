<?php
abstract class RM_Entity_Search_Abstract_Condition
    extends
        RM_Entity_Search_Abstract_Rules {

    /**
     * @var RM_Entity_Search_Condition[]
     */
    private $_searchConditions;

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
            if ($condition->isMatch()) {
                $condition->__copyFrom( $this );
                $conditions[] = $condition;
            }
        }
        return $conditions;
    }

    public function __setConditionToQuery(Zend_Db_Select $select) {
        foreach ($this->_getMatchedConditions() as $condition) {
            $condition->setSearchCondition( $select );
        }
    }

    /**
     * @param RM_Entity_Search_Abstract_Condition $search
     * @throws Exception
     */
    public function __copyFrom($search) {
        if ($search instanceof RM_Entity_Search_Abstract_Condition) {
            parent::__copyFrom( $search );
            $this->setCondition( $search->getConditions() );
        } else {
            throw new Exception('$search must be instance of RM_Entity_Search_Abstract_Condition');
        }
    }

}