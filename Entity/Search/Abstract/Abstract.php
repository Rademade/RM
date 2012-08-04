<?php
abstract class RM_Entity_Search_Abstract_Abstract {

    /**
     * @var RM_Entity_Search_Condition[]
     */
    protected $_conditions = array(  );

    private $_searchPhrase;

    /**
     * @abstract
     * @return RM_Entity_Search_Result_Interface[]
     */
    abstract public function getResults();

    public function addCondition(RM_Entity_Search_Condition $condition) {
        $this->_conditions[] = $condition;
    }

    public function setConditions(array $conditions) {
        $this->_conditions = array();
        foreach ($conditions as $condition) {
            $this->addCondition( $condition );
        }
    }

    /**
     * @return RM_Entity_Search_Condition[]
     */
    public function getConditions() {
        return $this->_conditions;
    }

    public function setPhrase($searchPhrase) {
        $searchPhrase = urldecode( trim( $searchPhrase) );
        $this->_searchPhrase = $searchPhrase;
    }

    public function getPhrase() {
        return $this->_searchPhrase;
    }

    /**
     * @param Zend_Db_Select $select
     */
    protected function __installQueryCondition(Zend_Db_Select $select) {
        $collectorCondition = $this->_getMergedCondition();
        if ($collectorCondition instanceof RM_Entity_Search_Condition) {
            $collectorCondition->improveQuery( $select );
        }
    }

    /**
     * @param RM_Entity_Search_Abstract_Abstract $search
     */
    public function __copyFrom(RM_Entity_Search_Abstract_Abstract $search) {
        $this->setPhrase( $search->getPhrase() );
        $this->setConditions( $search->getConditions() );
    }

    /**
     * @return RM_Entity_Search_Condition|null
     */
    private function _getMergedCondition() {
        if ( sizeof( $this->getConditions() ) === 0 ) {
            return null;
        } else {
            $collectorCondition = new RM_Entity_Search_Condition_Collector();
            $collectorCondition->mergeWithArray( $this->getConditions() );
            return $collectorCondition;
        }
    }

}