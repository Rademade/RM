<?php
abstract class RM_Entity_Search_Condition_Select
    extends
        RM_Entity_Search_Condition {

    /**
     * @return Zend_Db_Select
     */
    abstract protected function __getSelect();

    /**
     * @param Zend_Db_Select $select
     * @throws Exception
     * @return void
     */
    abstract public function setAutocompleteCondition(Zend_Db_Select $select);

    /**
     * @return RM_Entity_Search_Autocomplete_Result[]
     */
    public final function getResults() {
        $select = $this->__getSelect();
        $this->__setRulesToQuery( $select );
        $this->setAutocompleteCondition( $select );
        $select->limit(5);
        return $this->__fetchResults( $select );
    }

    /**
     * @param Zend_Db_Select $select
     * @return RM_Entity_Search_Autocomplete_Result[]
     */
    protected final function __fetchResults(Zend_Db_Select $select) {
        $data = RM_Entity::getDb()->fetchAll( $select );
        foreach ($data as &$row) {
            $row = $this->__initResultModel( $row );
        }
        return $data;
    }


}