<?php
class RM_Cassandra_LibraryLoader {

    private static $_loaded;

    public static function load() {
        if (self::$_loaded) return;

        if (!defined('LIBRARY_PATH')) {
            throw new Exception('Library path not defined');
        }

        if (!file_exists(LIBRARY_PATH . '/php-cassandra-binary')) {
            throw new Exception('php-cassandra-binary library not found');
        }

        function php_cassandra_binary_autoload($className) {
            if (strpos($className, 'evseevnn') !== 0) {
                return false;
            }
            $className = str_replace('evseevnn\\Cassandra\\', '', $className);
            $className = str_replace('\\', '/', $className);
            $scriptLocation = LIBRARY_PATH . '/php-cassandra-binary/' . $className . '.php';

            if ((file_exists($scriptLocation) === false) || (is_readable($scriptLocation) === false)) {
                return false;
            }

            require_once $scriptLocation;
        }

        spl_autoload_register('php_cassandra_binary_autoload');

        self::$_loaded = true;
    }

}