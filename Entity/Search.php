<?php
class RM_Entity_Search
    extends
        RM_Entity_Search_Entity {

    /**
     * @var RM_Entity_Search_Autocomplete
     */
    private $_autocompleteSearch;

    private $_currentSearchVariety;

    /**
     * @return RM_Entity_Search_Autocomplete
     */
    public function getAutocomplete() {
        if (!$this->_autocompleteSearch instanceof RM_Entity_Search_Autocomplete) {
            $this->_autocompleteSearch = new RM_Entity_Search_Autocomplete( );
            $this->_autocompleteSearch->setAutocompleteVarieties( $this->_getAutocompleteVarieties() );
        }
        return $this->_autocompleteSearch;
    }

    /**
     * @param string $type
     */
    public function setAutocompleteSearchType($type) {
        $type = urldecode( trim( $type ) );
        if (!empty($type)) {
            $this->_currentSearchVariety = $type;
        } else {
            $this->_currentSearchVariety = null;
        }
    }

    /**
     * @return string|null
     */
    public function getAutocompleteSearchType() {
        return $this->_currentSearchVariety;
    }

    /**
     * @param Zend_Db_Select $select
     */
    protected function __installQueryCondition(Zend_Db_Select $select) {
        $where = new RM_Query_Where();
        foreach ($this->_getMatchConditions() as $condition) {
            $condition->joinAutocompleteTable();
            $where->addSubOr( $condition->getWhereCondition( $this->getPhrase() ) );
        }
        parent::__installQueryCondition($select );
        $where->improveQuery( $select );
        $this->_groupSelectRows( $select );
    }

    private function _groupSelectRows(Zend_Db_Select $select) {
        $model = $this->_entityName;
        $select->group( join('.', array(
            $model::TABLE_NAME,
            $model::getKeyAttributeField()
        ) ) );
    }

    /**
     * Check is current search variety type is available and isSetted
     *
     * @param RM_Entity_Search_Autocomplete_Variety $variety
     * @return bool
     */
    private function _isResolveUseVariety(RM_Entity_Search_Autocomplete_Variety $variety) {
        return (
            is_null( $this->getAutocompleteSearchType() ) ||
            $this->getAutocompleteSearchType() ===  $variety->getType()
        );
    }

    /**
     * @param RM_Entity_Search_Condition_Entity $condition
     * @return RM_Entity_Search_Autocomplete_Variety
     */
    private function _getConditionVariety(RM_Entity_Search_Condition_Entity $condition) {
        $variety = $condition->getAutocompleteVariety( $this );
        return $variety;
    }


    /**
     * @return RM_Entity_Search_Condition_Entity[]
     */
    private function _getAutocompleteConditions() {
        $varieties = array();
        foreach ($this->getConditions() as $condition) {
            if ($condition instanceof RM_Entity_Search_Condition_Entity) {
                $varieties[] = $condition;
            }
        }
        return $varieties;
    }

    /**
     * @return RM_Entity_Search_Autocomplete_Variety[]
     */
    private function _getAutocompleteVarieties() {
        $varieties = array();
        foreach ($this->_getAutocompleteConditions() as $condition) {
            $varieties[] = $condition->getAutocompleteVariety( $this );
        }
        return $varieties;
    }

    /**
     * @return RM_Entity_Search_Condition_Entity[]
     */
    private function _getMatchConditions() {
        $conditions = array();
        foreach ($this->_getAutocompleteConditions() as $condition) {
            $variety = $this->_getConditionVariety( $condition );
            if ($variety->isMatch( $this->getPhrase() ) && $this->_isResolveUseVariety( $variety )) {
                $conditions[] = $condition;
            }
        }
        return $conditions;
    }


}
