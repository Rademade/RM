<?php
trait RM_Admin_BreadCrumb {

    protected $_addTitle = 'add';
    protected $_editTitle = 'edit';
    protected $_listTitle = 'list';

    protected $_addName;
    protected $_titleName;

    protected function getListCrumbName() {
        return ucfirst($this->_getAddName()) . ' ' . $this->_listTitle;
    }

    protected function getAddCrumbName() {
        return ucfirst($this->_addTitle) . ' ' . $this->_getAddName();
    }

    protected function getEditCrumbName() {
        return ucfirst($this->_editTitle) . ' ' . $this->_getAddName();
    }

    protected function _getAddName() {
        $name = is_string($this->_addName) ? $this->_addName : $this->_titleName;
        return mb_strtolower($name, 'utf-8');
    }

    protected function __setTitle($title) {
        $this->_titleName = $title;
        RM_View_Top::getInstance()->setTitle( $title );
        $this->view->headTitle($title); # =(
    }

}