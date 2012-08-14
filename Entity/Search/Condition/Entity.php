<?php
abstract class RM_Entity_Search_Condition_Entity
    extends
        RM_Entity_Search_Condition {

    /**
     * @var RM_Entity_Search_Autocomplete_Variety
     */
    private $_variety;

    /**
     * @param RM_Entity_Search_Abstract_Abstract $search
     * @return RM_Entity_Search_Autocomplete_Variety
     */
    public function getAutocompleteVariety(RM_Entity_Search_Abstract_Abstract $search) {
        if (!$this->_variety instanceof RM_Entity_Search_Autocomplete_Variety) {
            $className = $this->getAutocompleteVarietyClassName();
            $this->_variety = new $className();
        }
        if ($search instanceof RM_Entity_Search_Abstract_Abstract) {
            $this->_variety->__copyFrom( $search );
        }
        return $this->_variety;
    }

    /**
     * Get Variety class name for current conditions
     *
     * @abstract
     * @return string
     */
    abstract public function getAutocompleteVarietyClassName();

    /**
     * @abstract
     * @return void
     */
    abstract public function joinAutocompleteTable();

    /**
     * @abstract
     * @param string $phrase
     * @return RM_Query_Where
     */
    abstract public function getWhereCondition( $phrase );

}
