<?php
trait RM_Trait_Admin_Controller_Action_List {

    public function listAction() {
        if (!$this->__hasListPageAccess()) {
            $value = $this->__noListPageAccess();
            if (null !== $value) return $value;
        }
        parent::listAction();
        $this->__postEntities();
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

    protected function __noListPageAccess() {
        $this->__goBack();
        return false;
    }

    // RM_TODO better name
    protected function __generateVariableName() {
        $menu = $this->view->menu;
        $menu = Zend_Controller_Front::getInstance()->getDispatcher()->formatModuleName($menu);
        return lcfirst($menu);
    }

    protected function __postEntities() {
        $name = $this->__generateVariableName();
        $this->view->assign($name, $this->__findEntities($this->__getSearchQuery()));
    }

}