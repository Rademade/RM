<?php
class Zend_View_Helper_HasAccess {

    public function HasAccess(RM_User_Interface $user, $menuItem) {
        if (isset($menuItem['minRole'])) {
            return $user->getRole()->getHierarchy() <= $menuItem['minRole'];
        }
        return $user->getRole()->isAdmin();
    }

}