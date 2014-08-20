<?php
trait RM_Trait_Admin_Controller_Action_List {

    public function listAction() {
        if (!$this->__hasListPageAccess()) {
            $this->__goBack();
            return false;
        }
        parent::listAction();
        $menu = $this->view->menu;
        $menu = Zend_Controller_Front::getInstance()->getDispatcher()->formatModuleName($menu);
        $menu = lcfirst($menu);
        $this->view->assign($menu, $this->__findEntities($this->__getSearchQuery()));
        $this->__setupListPage();
        $this->__setupListPageCrumbs($this->__getCrumbs());
    }

    protected function __getSearchQuery() {
        return $this->getParam('search');
    }

    protected function __findEntities($query) {
        $itemClassName = $this->_itemClassName;
        return $itemClassName::getList();
    }

    protected function __setupListPage() {

    }

    protected function __setupListPageCrumbs() {

    }

    protected function __hasListPageAccess() {
        return true;
    }

}