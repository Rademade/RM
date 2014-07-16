<?php
class RM_Content_Field_Process_None
    extends
        RM_Content_Field_Process {

    private static $_self;

    /**
     * @static
     * @return RM_Content_Field_Process_None
     */
    public static function init() {
        if (!self::$_self instanceof self) {
            self::$_self = new self();
        }
        return self::$_self;
    }

    public function getInitialContent($html) {
        return $html;
    }

    public function getParsedContent($html) {
        return $html;
    }

}