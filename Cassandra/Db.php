<?php

RM_Cassandra_LibraryLoader::load();

use evseevnn\Cassandra;

class RM_Cassandra_Db {

    /**
     * @var Cassandra\Database
     */
    protected static $_db;

    final public static function get() {
        if (static::$_db) {
            return static::$_db;
        }
        return static::$_db = static::__open();
    }

    protected static function __open() {
        $cfg = Zend_Registry::get('cfg');
        $keyspace = trim($cfg['cassandra']['db']['keyspace'], '"\'');
        $db = new Cassandra\Database(['localhost'], '"' . $keyspace . '"');
        $db->connect();
        return $db;
    }

}