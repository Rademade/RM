<?php
class RM_Entity_Search_Autocomplete
    extends
        RM_Entity_Search_Abstract_Abstract {

    /**
     * @var RM_Entity_Search_Autocomplete_Variety[]
     */
    private $_autocompleteVarieties = array();

    public function addAutocompleteVariety(RM_Entity_Search_Autocomplete_Variety $variety) {
        $this->_autocompleteVarieties[] = $variety;
    }

    /**
     * @param RM_Entity_Search_Autocomplete_Variety[] $varieties
     */
    public function setAutocompleteVarieties(array $varieties) {
        $this->_autocompleteVarieties = array();
        foreach ($varieties as $variety) {
            $this->addAutocompleteVariety( $variety );
        }
    }

    /**
     * @return RM_Entity_Search_Autocomplete_Variety[]
     */
    public function getAutocompleteVarieties() {
        $varieties = array();
        foreach ($this->_autocompleteVarieties as $variety) {
            $varieties[] = $variety;
        }
        return $varieties;
    }

    /**
     * @return RM_Entity_Search_Autocomplete_Result[]
     */
    public function getResults() {
        $results = [];
        foreach ($this->getAutocompleteVarieties() as $autoCompleteVariety) {
            if ($autoCompleteVariety->isMatch()) {
                $results[] = $autoCompleteVariety->getResults();
            }
        }
        return empty($results) ? [] : call_user_func_array('array_merge', $results);
    }

}