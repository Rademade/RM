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

        function mb_lcfirst($string, $encoding = 'utf-8') {
            $strlen = mb_strlen($string, $encoding);
            $firstChar = mb_substr($string, 0, 1, $encoding);
            $then = mb_substr($string, 1, $strlen - 1, $encoding);
            return mb_strtolower($firstChar, $encoding) . $then;
        }

    }

}