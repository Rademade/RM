<?php
trait RM_Content_Field_Process_Singleton {

    protected static $_instance;

    /**
     * @return static
     */
    final public static function init() {
        return isset(static::$_instance)
            ? static::$_instance
            : static::$_instance = new static();
    }

    final private function __wakeup() {}

    final private function __clone() {}

}