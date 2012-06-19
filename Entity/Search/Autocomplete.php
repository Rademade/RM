<?php
class RM_Entity_Search_Autocomplete
    extends
        RM_Entity_Search_Abstract_Condition {

    /**
     * @abstract
     * @return RM_Entity_Search_Autocomplete_Result[]
     */
    public function getResults() {
        $results = array();
        foreach ($this->_getMatchedConditions() as $condition) {
            //TODO may add wise merge
            $results += $condition->getResults();
        }
        return $results;
    }


}
