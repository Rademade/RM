<?php
abstract class RM_Entity_Search_Condition
    implements
        RM_Query_Interface_ImproveSelect {

    /**
     * @var RM_Query_Join
     */
    private $_join;

    /**
     * @var RM_Query_Where
     */
    private $_where;

    /**
     * @var RM_Query_Order
     */
    private $_order;

    public function __construct() {
        $this->_where = new RM_Query_Where();
        $this->_order = new RM_Query_Order();
        $this->_join = new RM_Query_Join();
    }

    /**
     * @param Zend_Db_Select $select
     * @return void
     */
    public function improveQuery(Zend_Db_Select $select) {
        foreach ($this->_getQueryParts() as $queryPart) {
            $queryPart->improveQuery( $select );
        }
    }

    public function mergeWith(RM_Entity_Search_Condition $condition) {
        $this->_join->mergeWith( $condition->_getJoin() );
        $this->_where->mergeWith( $condition->_getWhere() );
        $this->_order->mergeWith( $condition->_getOrder() );
    }

    /**
     * @return RM_Query_Join
     */
    protected final function _getJoin() {
        return $this->_join;
    }

    /**
     * @return RM_Query_Order
     */
    protected final function _getOrder() {
        return $this->_order;
    }

    /**
     * @return RM_Query_Where
     */
    protected final function _getWhere() {
        return $this->_where;
    }

    /**
     * @param RM_Entity_Search_Condition $condition
     */
    public function __copyFrom(self $condition) {
        $this->_where = $condition->_getWhere();
        $this->_join = $condition->_getJoin();
        $this->_where = $condition->_getWhere();
    }

    /**
     * @return RM_Query_Interface_ImproveSelect[]
     */
    private function _getQueryParts() {
        return array(
            $this->_getJoin(),
            $this->_getWhere(),
            $this->_getOrder()
        );
    }

}