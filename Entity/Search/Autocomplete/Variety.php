<?php
abstract class RM_Entity_Search_Autocomplete_Variety
    extends
        RM_Entity_Search_Abstract_Abstract {

    /**
     * @abstract
     * @return bool
     */
    abstract public function isMatch();

    /**
     * @abstract
     * @return string
     */
    abstract public function getType();

    /**
     * @abstract
     * @return string
     */
    abstract public function getDescription();

    /**
     * @param string $value
     * @return RM_Entity_Search_Autocomplete_Result
     */
    protected function __initResultRow($value) {
        $resultRow = new RM_Entity_Search_Autocomplete_Result( $value );
        $resultRow->setType( $this->getType() );
        $resultRow->setDescription( $this->getDescription() );
        return $resultRow;
    }

}