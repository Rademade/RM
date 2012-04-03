<?php
class RM_View_Top {
	
	/**
	 * @var RM_View_Top
	 */
	private static $_self;
	/**
	 * @var Zend_View
	 */
	private $_view;
	/**
	 * @var Zend_Controller_Request_Abstract
	 */
	private $_request;
	/**
	 * @var RM_System_Breadcrumbs
	 */
	private $_breadcrumbs;
	private $_title = '';
	/**
	 * @var array
	 */
	private $_buttons = array();

	private $_autoAdd = false;
	private $_autoAddSearchUrl;
	private $_autoAddSubmitUrl;
	private $_listType;
	private $_search = false;
	private $_searchUrl;

	public static function getInstance() {
		if (!(self::$_self instanceof self)) {
			self::$_self= new self();
			self::$_self->_init();
		}
		return self::$_self;
	}
	
	public function _init() {
		$this->_view = Zend_Layout::getMvcInstance()->getView();
		$this->_request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_breadcrumbs = new RM_System_Breadcrumbs();
	}
	
	public function getBreadcrumbs() {
		return $this->_breadcrumbs;
	}
	
	public function getTitle() {
		return $this->_title;
	}

	public function setTitle( $title ) {
		$this->_title = $title;
		return $this;
	}

	public function addButton(RM_View_Element_Button $button) {
		$this->_buttons[] = $button;
		return $this;
	}
	
	public function getButtons( ) {
		return $this->_buttons;
	}

	public function setAutoAdd($searchUrl, $submitUrl, $listType) {
		$this->_autoAddSearchUrl = $searchUrl;
		$this->_autoAddSubmitUrl = $submitUrl;
		$this->_listType = $listType;
		$this->_autoAdd = true;
	}

	public function isAutoAdd() {
		return $this->_autoAdd;
	}

	public function isSearch() {
		return $this->_search;
	}

	public function addSearch($searchUrl) {
		$this->_search = true;
		$this->_searchUrl = $searchUrl;
	}

	public function getSearchUrl() {
		return $this->_searchUrl;
	}

	public function getSearchAutoAddUrl() {
		return $this->_autoAddSearchUrl;
	}

	public function getSubmitAutoAddUrl() {
		return $this->_autoAddSubmitUrl;
	}

	public function getType() {
		return $this->_listType;
	}

}