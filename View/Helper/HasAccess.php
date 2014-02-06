<?php
class Zend_View_Helper_HasAccess {

    public function HasAccess(RM_User_Interface $user, $menuItem, $defaultRole = null) {
        if (isset($menuItem['minRole'])) {
            return $user->getRole()->hasAccess($menuItem['minRole']);
        }
        if (!is_null($defaultRole)) {
            return $user->getRole()->hasAccess($defaultRole);
        }
        return $user->getRole()->isAdmin();
    }

}