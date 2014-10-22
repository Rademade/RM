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
        $idAttribute = $itemClassName::TABLE_NAME . '.' . $itemClassName::getKeyAttributeField();
        $order = new RM_Query_Order();
        $order->add($idAttribute, 'DESC');
        return $itemClassName::getList($order, $this->__getSearchLimits());
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

    protected function __getPage() {
        return (int)$this->view->page;
    }

    protected function __getSearchLimits() {
        $limits = new RM_Query_Limits(19);
        $limits->setPageRange(15);
        $limits->setPage($this->__getPage());
        return $limits;
    }

}