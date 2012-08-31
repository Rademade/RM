<?php
abstract class RM_Entity_Search_Repository {

    /**
     * Return class name instanceof RM_Entity
     *
     * @abstract
     * @return string
     */
    abstract protected function __getEntityClassName();

    /**
     * Return class instanceof RM_Entity_Search_Condition
     *
     * @abstract
     * @return RM_Entity_Search_Condition
     */
    abstract protected function __getConditionClass();

    /**
     * @param RM_Entity_Search_Condition $condition
     * @return RM_Entity_Search_Entity
     */
    protected function __getEntitySearch(
        RM_Entity_Search_Condition $condition
    ) {
        $search = new RM_Entity_Search_Entity( $this->__getEntityClassName() );
        $search->addCondition( $condition );
        return $search;
    }


}