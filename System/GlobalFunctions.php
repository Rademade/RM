<?php
class RM_System_GlobalFunctions {

    public static function init() {

        if (self::$_initialized) return;

        self::$_initialized = true;

        function rm_isset(&$data, $key, $default = null) {
            if ( is_array($data) ) {
                return isset($data[$key]) ? $data[$key] : $default;
            } elseif ( is_object($data) ) {
                return isset($data->{$key}) ? $data->{$key} : $default;
            }
            return $default;
        }

        function mb_slice_first($string, $encoding = 'utf-8') {
            return mb_substr($string, 1, mb_strlen($string, $encoding) - 1, $encoding);
        }

        function mb_first($string, $encoding = 'utf-8') {
            return mb_substr($string, 0, 1, $encoding);
        }

        function mb_lcfirst($string, $encoding = 'utf-8') {
            return mb_strtolower(mb_first($string, $encoding), $encoding) . mb_slice_first($string, $encoding);
        }

        function mb_ucfirst($string, $encoding = 'utf-8') {
            return mb_strtoupper(mb_first($string, $encoding), $encoding) . mb_slice_first($string, $encoding);
        }

        function utf8_tolower($string) {
            return mb_strtolower($string, 'utf-8');
        }

        function mb_str_replace($needle, $replacement, $haystack) {
            return implode($replacement, mb_split($needle, $haystack));
        }

    }

    private static $_initialized = false;

}