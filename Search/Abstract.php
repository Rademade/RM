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
        if (!empty($conditions)) {
            $this->_select->where( $this->_db->quoteInto(
                join(' OR ', $conditions),
                trim( '%' . $this->_searchWorld . '%' )
            ) );
        }
    }

    public function setId($id) {
        $this->_select->where('idOrder = ?', (int)$id);
    }

    public function getResults() {
        $list = RM_Query_Exec::select(
            $this->_select,
            func_get_args()
        );
        $model = static::SEARCH_MODEL;
        foreach ($list as &$order) {
            $order = new $model($order);
        }
        return $list;
    }

}