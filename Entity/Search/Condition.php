<?php
abstract class RM_Entity_Search_Condition
    implements
        RM_Query_Interface_ImproveSelect,
        RM_Query_Interface_Hashable {

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

    public function __clone() {
        $this->_where = clone $this->_where;
        $this->_order = clone $this->_order;
        $this->_join = clone $this->_join;
        $this->_having = clone $this->_having;
        $this->_group = clone $this->_group;
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

    public function improveQueryWithTypes(Zend_Db_Select $select, array $types = []) {
        foreach ($this->_getQueryParts() as $queryPart) {
            if (in_array(get_class($queryPart), $types)) {
                $queryPart->improveQuery( $select );
            }
        }
    }

    public function isHashable() {
        return true;
    }

    public function getHash() {
        $hashes = [];
        foreach ($this->_getQueryParts() as $queryPart) {
            if ($queryPart instanceof RM_Query_Interface_Hashable) {
                $hashes[] = $queryPart->getHash();
            }
        }
        return md5(serialize($hashes));
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
    protected function _getQueryParts() {
        return array(
            $this->_getJoin(),
            $this->_getWhere(),
            $this->_getOrder(),
            $this->_getHaving(),
            $this->_getGroup()
        );
    }

    protected function __beforeMerge() {
        //empty
    }

}