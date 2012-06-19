<?php
abstract class RM_Entity_Search_Condition
    extends
        RM_Entity_Search_Abstract_Rules {

    /**
     * @abstract
     * @return bool
     */
    abstract public function isMatch();

    /**
     * @abstract
     * @param stdClass $data
     * @return RM_Entity_Search_Autocomplete_Result
     */
    abstract protected function __initResultModel(stdClass $data);

    /**
     * @abstract
     * @return Zend_Db_Select
     */
    abstract protected function __getSelect();


    /**
     * @abstract
     * @param Zend_Db_Select $select
     * @return void
     */
    abstract public function setSearchCondition(Zend_Db_Select $select);

    /**
     * @abstract
     * @param Zend_Db_Select $select
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