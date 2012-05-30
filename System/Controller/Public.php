<?php
/**
 * @deprecated
 */
abstract class RM_System_Controller_Public
	extends
        Zend_Controller_Action {

	/**
	 * @var RM_User
	 */
	protected $_user;
	protected $_idUser;

	/**
	 * @var RM_User_Session
	 */
	protected $_userSession;

	/**
	 * @var Application_Model_Watcher
	 */
	protected $_userWatcher;

	/**
	 * @var RM_Page
	 */
	protected $_page;

	/**
	 * @var Application_Model_User_Cart
	 */
	protected $_cart;

	private $_idPage;

	public function preDispatch() {
		$this->_userSession = RM_User_Session::getInstance();
		$this->_user = $this->_userSession->getUser();
		$this->_idUser = $this->_user instanceof RM_User ? $this->_user->getId() : 0;
		$this->_cart = Application_Model_User_Cart::getInstance();
		$this->_userWatcher = Application_Model_Watcher::getInstance();
		$this->_idPage = (int)$this->_getParam('idPage');
		if ($this->_idPage !== 0) {
			$this->_page = RM_Page::getById( $this->_idPage );
            if ($this->_page instanceof RM_Interface_Contentable) {
    			$this->_initMeta( $this->_page);
            }
		}
	}

	protected function _initMeta(RM_Interface_Contentable $page){
		$this->view->headTitle( $page->getContent()->getPageTitle() );
		$this->view->headMeta()->appendName('keywords', $page->getContent()->getPageKeywords());
		$this->view->headMeta()->appendName('description', $page->getContent()->getPageDesc());
	}

    protected function __disableView() {
        $this->_helper->layout()->disableLayout(true);
        $this->_helper->viewRenderer->setNoRender(true);
    }

	public function postDispatch() {
		$this->view->assign(array(
			'user' => $this->_user,
			'page' => $this->_page,
		    'cart' => $this->_cart
		));
	}

}