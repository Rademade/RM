<?php
class RM_Entity_Search_Condition_Collector
    extends
        RM_Entity_Search_Condition {

    /**
     * @param RM_Entity_Search_Condition[] $conditions
     */
    public function mergeWithArray(array $conditions) {
        foreach ($conditions as $condition) {
            $this->mergeWith( $condition );
        }
    }

    /**
     * @param RM_Entity_Search_Condition $condition
     */
    public function mergeWith(RM_Entity_Search_Condition $condition) {
        $condition->__beforeMerge();
        $this->_getJoin()->mergeWith( $condition->_getJoin() );
        $this->_getWhere()->mergeWith( $condition->_getWhere() );
        $this->_getOrder()->mergeWith( $condition->_getOrder() );
        $this->_getHaving()->mergeWith( $condition->_getHaving() );
        $this->_getGroup()->mergeWith( $condition->_getGroup() );
    }

}