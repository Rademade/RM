<?php
/**
 * @deprecated
 */
abstract class RM_System_Controller_Public
	extends
        RM_Controller_Public {

	/**
	 * @var Application_Model_User_Cart
	 */
	protected $_cart;

    /**
     * @var Application_Model_Watcher
     */
    protected $_userWatcher;

    /**
     * @var RM_User_Session
     */
    protected $_userSession;
 protected $_footerPages;

    public function preDispatch() {
        parent::preDispatch();
		$this->_cart = Application_Model_User_Cart::getInstance();
		$this->_userWatcher = Application_Model_Watcher::getInstance();
        $this->_userSession = RM_User_Session::getInstance();
        $this->_footerPages = Application_Model_Page::getFooterPages();
    }

	public function postDispatch() {
        parent::postDispatch();
		$this->view->assign(array(
		    'cart' => $this->_cart,
            'footerItems' => $this->_footerPages
		));
	}

}