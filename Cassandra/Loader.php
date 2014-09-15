<?php
class RM_Cassandra_Loader {

    private static $_loaded;

    public static function load() {
        if (self::$_loaded) return;

        if (!defined('LIBRARY_PATH')) {
            throw new Exception('Library path not defined');
        }

        $loader = LIBRARY_PATH . '/PhpCassa/autoload.php';

        if (!file_exists($loader)) {
            throw new Exception('Cassandra library not found');
        }

        require_once $loader;

        self::$_loaded = true;
    }

}



