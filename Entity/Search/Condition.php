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
     * @param RM_Query_Where $where
     * @return void
     */
    abstract public function setSearchConditions(RM_Query_Where &$where);

    /**
     * @abstract
     * @param Zend_Db_Select $select
     * @return void
     */
    abstract public function setSearchJoins(Zend_Db_Select $select);

    /**
     * @abstract
     * @return string
     */
    abstract public function getConditionType();

    /**
     * @return string
     */
    public function getConditionDescription() {
        return $this->getConditionType();
    }

    /**
     * @param stdClass $data
     * @return RM_Entity_Search_Autocomplete_Result
     */
    protected function __initResultModel(stdClass $data) {
        $result = new RM_Entity_Search_Autocomplete_Result($data);
        $result->setDescription( $this->getConditionDescription() );
        $result->setType( $this->getConditionType() );
        return $result;
    }

}