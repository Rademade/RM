<?php
class RM_System_GlobalFunctions {

    public static function init() {

        function rm_isset(&$data, $key, $default = null) {
            if ( is_array($data) ) {
                return isset($data[$key]) ? $data[$key] : $default;
            } elseif ( is_object($data) ) {
                return isset($data->{$key}) ? $data->{$key} : $default;
            }
            return $default;
        }

    }

}