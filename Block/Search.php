<?php
class RM_Block_Search
    extends
        RM_Search_Abstract {

    const SEARCH_MODEL = 'RM_Block';

    /**
     * TODO refactor
     * @return array
     */
    protected function _getSearchConditions() {
        $conditions = array();
        if (intval($this->_searchWorld) == $this->_searchWorld && intval($this->_searchWorld) !== 0) {
            $conditions[] = RM_Block::TABLE_NAME . '.idBlock LIKE ?';
        }
        if (strlen($this->_searchWorld) > 2) {
            $this->_select->join('fieldsContent', 'fieldsContent.idContent = blocks.idContent', array());
            $this->_select->group('idBlock');
            $this->_select->where(
                'idFieldName = ?',
                RM_Content_Field_Name::getByName('name')->getId()
            );
            $conditions[] = "fieldsContent.fieldContent LIKE ?";
        }
        return $conditions;
    }

    public function setIdPage($id) {
        $this->_select->where('blocks.idPage = ?', $id);
    }

    public function setSearchType($searchType) {
        $this->_select->where('blocks.searchType = ?', $searchType);
    }

    public function onlyShowStatus() {
        $this->_select->where('blocks.blockStatus = ?', RM_Block::STATUS_SHOW);
    }

}
