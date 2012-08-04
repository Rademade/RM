<?php
abstract class RM_Entity_Search_Autocomplete_Variety_Query
    extends
        RM_Entity_Search_Autocomplete_Variety {

    /**
     * @var RM_Entity_Search_Condition_Autocomplete
     */
    private $_autocompleteCondition;

    const FIELD_NAME = 'autocompleteValue';

    /**
     * @return RM_Entity_Search_Autocomplete_Result[]
     */
    public function getResults() {
        $select = $this->__getSelect();
        $this->__installQueryCondition( $select );
        return $this->__initAutocompleteResults( $select );
    }

    /**
     * @abstract
     * @return string
     */
    abstract protected function __getAutocompleteTableName();

    /**
     * @abstract
     * @return string
     */
    abstract protected function __getAutocompleteFieldName();

    /**
     * Get autocomplete variety condition class name
     *
     * @abstract
     * @return RM_Entity_Search_Condition_Autocomplete
     */
    abstract protected function __getAutocompleteCondition();

    /**
     * @param Zend_Db_Select $select
     * @throws Exception
     */
    protected final function __installQueryCondition(Zend_Db_Select $select) {
        $this->addCondition( $this->_getCompleteCondition() );
        parent::__installQueryCondition( $select );
        $select->limit(5);
    }

    /**
     * @return Zend_Db_Select
     */
    protected function __getSelect() {
        $select = RM_Entity::getDb()->select();
        $select->from( $this->__getAutocompleteTableName(), array(
            self::FIELD_NAME => $this->__getAutocompleteFieldName()
        ) );
        return $select;
    }

    /**
     * Init array of varieties results
     *
     * @param Zend_Db_Select $select
     * @return RM_Entity_Search_Autocomplete_Result[]
     */
    protected function __initAutocompleteResults(Zend_Db_Select $select) {
        $result = array();
        foreach ( RM_Entity::getDb()->fetchAll( $select ) as $row ) {
            $result[] = $this->__initResultRow(
                $row->{ self::FIELD_NAME }
            );
        }
        return $result;
    }

    /**
     * @throws Exception
     * @return RM_Entity_Search_Condition_Autocomplete
     */
    private function _getCompleteCondition() {
        $condition = $this->__getAutocompleteCondition();
        if (!$condition instanceof RM_Entity_Search_Condition_Autocomplete) {
            throw new Exception('__getAutocompleteCondition() must return RM_Entity_Search_Condition_Autocomplete');
        }
        $condition->joinEntityTable();
        $condition->installAutocompleteCondition( $this->getPhrase() );
        return $condition;
    }
}