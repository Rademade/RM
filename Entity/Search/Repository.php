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
     * RM_Entity_Search_Condition $condition [, RM_Entity_Search_Condition $... ]
     *
     * @return RM_Entity_Search_Entity
     */
    protected function __getEntitySearch() {
        $search = new RM_Entity_Search_Entity( $this->__getEntityClassName() );
        foreach (func_get_args() as $condition) {
            $search->addCondition($condition);
        }
        return $search;
    }


}