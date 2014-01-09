<?php
class RM_Entity_Search_Condition_Collector_Merger
    extends
        RM_Entity_Search_Condition_Collector {

    protected $_isMergedConditions;

    public function getConditions() {
        return array();
    }

    protected function _getQueryParts() {
        $this->_mergeConditions();
        return parent::_getQueryParts();
    }

    protected function __beforeMerge() {
        $this->_mergeConditions();
    }

    public function _mergeConditions() {
        if (!$this->_isMergedConditions) {
            $this->mergeWithArray($this->getConditions());
            $this->_isMergedConditions = true;
        }
    }

}