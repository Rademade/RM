<?php
class RM_Cassandra_LibraryLoader {

    private static $_loaded;

    public static function load() {
        if (self::$_loaded) return;

        if (!defined('LIBRARY_PATH')) {
            throw new Exception('Library path not defined');
        }

        $dir = LIBRARY_PATH . '/php-cassandra-binary';

        if (!file_exists($dir)) {
            throw new Exception('php-cassandra-binary library not found');
        }

        function php_cassandra_binary_autoload($className) {
            $className = str_replace('evseevnn\\Cassandra\\', '', $className);
            $className = str_replace('\\', '/', $className);
            require_once LIBRARY_PATH . '/php-cassandra-binary/' . $className . '.php';
        }

        spl_autoload_register('php_cassandra_binary_autoload');

        self::$_loaded = true;
    }

}