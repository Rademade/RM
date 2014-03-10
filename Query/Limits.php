<?php
class RM_Query_Limits
	implements
		RM_Query_Interface_Hashable {
	
	private $_limit;
	private $_pageRange = 10;
	private $_page;
    private $_offset;

    public static function get( $limit ) {
        return new self( $limit );
    }

	public function __construct($limit) {
		$this->_limit = (int)$limit;
	}

	public function setPageRange($range) {
		$this->_pageRange = (int)$range;
		return $this;
	}
	
	public function setPage($page) {
		$this->_page = (int)$page;
		return $this;
	}

    public function setOffset($offset) {
        $this->_offset = $offset;
    }
	
	public function getHash() {
		return join('_', array(
			$this->_limit,
			$this->_page,
			$this->_pageRange
		));
	}

	public function isHashable(){
		return true;
	}

	public function getLimit() {
		return $this->_limit;
	}

    public function getOffset() {
        return $this->_offset;
    }
	
	public function getPageRange() {
		return $this->_pageRange;
	}
	
	public function getPage() {
		return $this->_page;
	}

	public function _setPaginatorParams(Zend_Paginator $paginator) {
		$paginator->setItemCountPerPage( $this->getLimit() );
		$paginator->setPageRange( $this->getPageRange() );
		$paginator->setCurrentPageNumber( $this->getPage() );
	}

	/**
	 * @param array $items
	 * @return array|Zend_Paginator
	 */
	public function getPaginator(array $items) {
		if (is_int($this->getPage())) {
			$items = Zend_Paginator::factory( $items );
			$this->_setPaginatorParams( $items );
		}
		return $items;
	}

	public function getResult(Zend_Db_Select $select) {
		if (is_int($this->getPage())) {
			$items = Zend_Paginator::factory( $select );
			$this->_setPaginatorParams( $items );
		} else {
            if ($this->getLimit() || $this->getOffset()) {
                $select->limit( $this->getLimit(), $this->getOffset() );
            }
			$items = $select->getAdapter()->fetchAll( $select );
		}
		return $items;
	}

}