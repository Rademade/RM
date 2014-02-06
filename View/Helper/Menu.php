<?php
class RM_View_Helper_Menu {

    private $_view;

    public function getMenuHTML(RM_User_Interface $currentUser, $currentMenu, array $menuItems, $defaultRole = null) {
        return $this->_getView()->assign(array(
            'menuItems' => $menuItems,
            'currentMenu' => $currentMenu,
            'currentUser' => $currentUser,
            'defaultRole' => $defaultRole
        ))->render('menu.phtml');
    }

    private function _getView() {
        if (!$this->_view instanceof RM_View) {
            $this->_view = new RM_View();
        }
        return $this->_view;
    }

}