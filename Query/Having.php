<?php
class RM_Query_Having
    extends
        RM_Query_Where {

    public function improveQuery(Zend_Db_Select $select) {
        if (!empty($this->_conditions)) {
            $select->having( $this->_getConditionSQL() );
        }
    }

}