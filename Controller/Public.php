<?php
class RM_Controller_Public
    extends
        RM_Controller_Base_Abstract {

    protected $_page;
    protected $_metaAutoAppend = true;
    protected $_displayCheck = true;
    private $_idPage;

    public function preDispatch() {
        parent::preDispatch();
        $this->_idPage = (int)$this->_getParam('idPage');
        if ($this->_idPage !== 0) {
            $pageModel = RM_Dependencies::getInstance()->pageClass;
            $this->_page = $pageModel::getById( $this->_idPage );
            if ($this->_page instanceof RM_Interface_Hideable && $this->_displayCheck && !$this->_page->isShow()) {
                $this->redirect('/');
            }
            if ($this->_metaAutoAppend) $this->_initMeta( $this->_page);
        }
    }

    protected function _initMeta($page) {
        if ($page instanceof RM_Interface_Contentable) {
            $this->view->headTitle( $page->getContent()->getPageTitle() );
            $this->view->headMeta()->appendName('keywords', $page->getContent()->getPageKeywords());
            $this->view->headMeta()->appendName('description', $page->getContent()->getPageDesc());
        }
    }

    public function postDispatch() {
        parent::postDispatch();
        if (is_null($this->view->page)) {
            $this->view->assign('page', $this->_page);
        }
    }

}