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

        function number_between($num, $min, $max, $strict = true) {
            if ($strict) return $min < $num && $num < $max;
            return $min <= $num && $num <= $max;
        }

        function any_of($list, $pred) {
            if ($pred instanceof Closure) {
                foreach ($list as $key => $value) {
                    if ($pred($value, $key)) return true;
                }
            } else {
                foreach ($list as $key => $value) {
                    if (call_user_func($pred, $value)) return true;
                }
            }
            return false;
        }

		function invoke($list, $method, $args = []) {
			foreach ($list as $item) {
				call_user_func_array([$item, $method], $args);
			}
		}

        function browser_log() {
            $args = func_get_args();
            if (empty($args)) return;
            ob_start();
            ?><script type="text/javascript">
                window.console || (window.console = {});
                window.console.log || (window.console.log = function() {});<?php
                foreach ($args as $arg) {
                    ?>console.log(<?=Zend_Json::encode($arg)?>);<?php
                }
            ?></script><?php
            echo ob_get_clean();
        }

    }

    private static $_initialized = false;

}