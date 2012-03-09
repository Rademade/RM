<?php
class RM_Query_Limits
	implements
		RM_Query_Interface_Hashable {
	
	private $_limit;
	private $_pageRange = 10;
	private $_page;
	
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
	
	public function getPageRange() {
		return $this->_pageRange;
	}
	
	public function getPage() {
		return $this->_page;
	}
	
	public function getResult(Zend_Db_Select $select) {
		if (is_int($this->getPage())) {
			$items = Zend_Paginator::factory( $select );
			$items->setItemCountPerPage( $this->getLimit() );
			$items->setPageRange( $this->getPageRange() );
			$items->setCurrentPageNumber( $this->getPage() );
		} else {
			if ($this->getLimit() !== 0) {
				$select->limit( $this->getLimit() );
			}
			$items =  Zend_Registry::get('db')->fetchAll( $select );
		}
		return $items;
	}

}