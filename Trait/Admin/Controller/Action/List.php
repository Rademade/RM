<?php
trait RM_Trait_Admin_Controller_Action_List {

    public function listAction() {
        if (!$this->__hasListAccess()) {
            $this->__goBack();
            return false;
        }
        parent::listAction();
        $this->__setupListCrumbs();
        $menu = $this->view->menu;
        $menu = Zend_Controller_Front::getInstance()->getDispatcher()->formatModuleName($menu);
        $menu = lcfirst($menu);
        $this->view->assign(array(
            $menu => $this->__findEntities($this->__getSearchQuery())
        ));
    }

    protected function __getSearchQuery() {
        return $this->getParam('search');
    }

    protected function __findEntities($query) {
        $itemClassName = $this->_itemClassName;
        return $itemClassName::getList();
    }

    protected function __setupListCrumbs() {

    }

    protected function __hasListAccess() {
        return true;
    }

}