<?php
abstract class RM_Entity_Search
    extends
        RM_Entity_Search_Abstract_Condition {

    /**
     * @var RM_Entity
     */
    protected $_entityName = '';

    /**
     * @var RM_Entity_Search_Autocomplete
     */
    private $_autocomplete;

    /**
     * @return Zend_Db_Select
     */
    protected function __getSelect() {
        $select = call_user_func( array(
            $this->_entityName,
            '_getSelect'
        ) );
        $this->__setRulesToQuery( $select );
        $this->__setConditionToQuery( $select );
        return $select;
    }

    /**
     * @return RM_Entity[]
     */
    public function getResults() {
        return call_user_func_array( array(
            $this->_entityName,
            '_initList'
        ), array(
            $this->__getSelect(),
            func_get_args()
        ) );
    }

    /**
     * @return RM_Entity
     */
    public function getFirst() {
        return call_user_func_array( array(
            $this->_entityName,
            '_initItem'
        ), array(
            $this->__getSelect(),
            func_get_args()
        ) );
    }

    public function getCount() {
        $model = $this->_entityName;
        return RM_Query_Exec::getRowCount(
            $this->__getSelect(),
            join( '.', array(
                $model::TABLE_NAME,
                $model::getKeyAttributeField()
            ) )
        );
    }

    public function getAutocomplete() {
        if (!$this->_autocomplete instanceof RM_Entity_Search_Autocomplete) {
            $this->_autocomplete = new RM_Entity_Search_Autocomplete();
            $this->_autocomplete->__copyFrom( $this );
        }
        return $this->_autocomplete;
    }

}