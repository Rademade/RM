<?php
abstract class RM_Entity_Search_Rules {

    /**
     * @var RM_Query_Interface_ImproveSelect[]
     */
    private $_rules = array();

    protected function __addRule(RM_Query_Interface_ImproveSelect $rule) {
        $this->_rules[] = $rule;
    }

    public function improveSelect(Zend_Db_Select $select) {
        foreach ($this->_rules as $rule) {
            if ($rule instanceof RM_Query_Interface_ImproveSelect) {
                $rule->improveQuery( $select );
            }
        }
    }

}