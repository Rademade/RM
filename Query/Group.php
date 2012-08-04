<?php
class RM_Query_Group
    implements
        RM_Query_Interface_ImproveSelect {

    private $_groupFields = array();

    public function add($field) {
        $field = trim( $field );
        if ($field != '') {
            if (!in_array($field, $this->_groupFields)) {
                $this->_groupFields[] = $field;
            }
        }
    }

    /**
     * @param RM_Query_Group $group
     */
    public function mergeWith(self $group) {
        foreach ($group->_groupFields as $field) {
            $this->add( $field );
        }
    }

    public function improveQuery(Zend_Db_Select $select) {
        if (!empty($this->_groupFields)) {
            $select->group( $this->_groupFields );
        }
    }
}