<?php
class RM_Search_Repository {

    const CLASS_NAME = null;

    protected static function __callSelect($funcName, $args) {
        return call_user_func_array(
            array(
                static::CLASS_NAME,
                $funcName
            ),
            $args
        );
    }

}