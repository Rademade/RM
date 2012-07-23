<?php
abstract class RM_Entity_Search_Condition_Autocomplete
    extends
        RM_Entity_Search_Condition {

    /**
     * Set condition for finding autocomplete results
     *
     * @abstract
     * @param string $phrase
     * @return void
     */
    abstract public function installAutocompleteCondition($phrase);

    /**
     * Join tables from AutocompleteTable to EntityTable
     *
     * @abstract
     * @return void
     */
    abstract public function joinEntityTable();

}