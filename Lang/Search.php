<?php
class RM_Lang_Search
    extends
        RM_Search_Abstract {

    const SEARCH_MODEL = 'RM_Lang';

    protected function _getSearchConditions() {
        $conditions = array();
        if (intval($this->_searchWorld) == $this->_searchWorld && intval($this->_searchWorld) !== 0) {
            $conditions[] = RM_Lang::TABLE_NAME . '.idLang LIKE ?';
        }
        if (strlen($this->_searchWorld) > 2) {
            $conditions[] = RM_Lang::TABLE_NAME . '.langName LIKE ?';
        }
        return $conditions;
    }

    public function onlyShow() {
        $this->_select->where('langs.langStatus = ?', RM_Lang::STATUS_SHOW);
    }

}