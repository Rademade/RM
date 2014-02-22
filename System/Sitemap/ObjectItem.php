<?php
// todo redo to trait
abstract class RM_System_Sitemap_ObjectItem {

    private static $_urlHelper;

    protected static function _getUrlHelper() {
        if (!self::$_urlHelper instanceof Zend_View_Helper_Url) {
            self::$_urlHelper = new Zend_View_Helper_Url();
        }
        return self::$_urlHelper;
    }

}