<?php
abstract class RM_Entity_Search_Autocomplete_Variety_Query
    extends
        RM_Entity_Search_Autocomplete_Variety {

    const FIELD_ID = 'autocompleteId';
    const FIELD_NAME = 'autocompleteValue';

    protected static function __getDb() {
        return RM_Entity_Db::getInstance()->getConnection( get_called_class() );
    }

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
        $baseConditions = $this->__getBaseConditions();
        if ($baseConditions instanceof RM_Entity_Search_Condition) {
            $this->unshiftCondition( $baseConditions );
        }
        $this->addCondition( $this->_getCompleteCondition() );
        parent::__installQueryCondition( $select );
    }

    /**
     * @return Zend_Db_Select
     */
    protected function __getSelect() {
        $select = static::__getDb()->select();
        $select->from(
            $this->__getAutocompleteTableName(),
            array_merge(
                [static::FIELD_NAME => $this->__getAutocompleteFieldName()],
                $this->__getAutocompleteItemFields()
            )
        );
        $select->group(static::FIELD_NAME);
        $select->limit(10);
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
        foreach ( $select->getAdapter()->fetchAll( $select ) as $row ) {
            $autoCompleteItem = $this->__initResultRow( $row->{ static::FIELD_NAME } );
            if ( isset( $row->{ static::FIELD_ID } ) ) {
                $autoCompleteItem->setId( $row->{ static::FIELD_ID } );
            }
            $result[] = $autoCompleteItem;
        }
        return $result;
    }

    protected function __getBaseConditions() {
        return null;
    }

    protected function __getAutocompleteItemFields() {
        return array();
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