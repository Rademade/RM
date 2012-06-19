<?php
abstract class RM_Search_Abstract {

    protected $_searchWorld;

    /**
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * @var RM_Entity
     */
    const SEARCH_MODEL = null;

    public function __construct() {
        $model = static::SEARCH_MODEL;
        $this->_select =  $model::_getSelect();
        $this->_db = Zend_Registry::get('db');
    }

    abstract protected function _getSearchConditions();

    public function setMatch($matchText) {
        $this->_searchWorld = $matchText;
        $conditions = $this->_getSearchConditions();
        if (!empty($conditions) && strlen(trim($this->_searchWorld)) > 1) {
            $this->_select->where( $this->_db->quoteInto(
                join(' OR ', $conditions),
                trim( '%' . $this->_searchWorld . '%' )
            ) );
        }
    }

    public function setId($id) {
        $model = static::SEARCH_MODEL;
        $this->_select->where($model::getKeyAttributeField() . ' = ?', (int)$id);
    }

    function __clone() {
        $this->_select = clone $this->_select;
    }

    public function getResults() {
        $model = static::SEARCH_MODEL;
        return $model::_initList( $this->_select, func_get_args() );
    }

    public function getFirst() {
        $model = static::SEARCH_MODEL;
        return $model::_initItem( $this->_select, func_get_args() );
    }

    public function getCount() {
        $model = static::SEARCH_MODEL;
        return RM_Query_Exec::getRowCount(
            $this->_select,
            join('.', array(
                $model::TABLE_NAME,
                $model::getKeyAttributeField()
            ))
        );
    }

    public function sortLastAdded() {
        $model = static::SEARCH_MODEL;
        $this->_select->order($model::TABLE_NAME . '.' . $model::getKeyAttributeField(). ' DESC');
    }

}