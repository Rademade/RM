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

    /**
     * @var RM_Query_Having
     */
    private $_having;

    /**
     * @var RM_Query_Group
     */
    private $_group;

    public function __construct() {
        $this->_where = new RM_Query_Where();
        $this->_order = new RM_Query_Order();
        $this->_join = new RM_Query_Join();
        $this->_having = new RM_Query_Having();
        $this->_group = new RM_Query_Group();
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
     * @return RM_Query_Having
     */
    protected final function _getHaving() {
        return $this->_having;
    }

    /**
     * @return RM_Query_Group
     */
    protected  final function _getGroup() {
        return $this->_group;
    }

    /**
     * @return RM_Query_Interface_ImproveSelect[]
     */
    private function _getQueryParts() {
        return array(
            $this->_getJoin(),
            $this->_getWhere(),
            $this->_getOrder(),
            $this->_getHaving(),
            $this->_getGroup()
        );
    }

}