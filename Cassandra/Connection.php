<?php

RM_Cassandra_Loader::load();

use phpcassa\Connection\ConnectionPool;
use phpcassa\UUID;

class RM_Cassandra_Connection {

    /**
     * @var ConnectionPool
     */
    protected static $_connection;

    final public static function connect() {
        if (static::$_connection) {
            return static::$_connection;
        }
        return static::$_connection = static::__connect();
    }

    protected static function __connect() {
        $cfg = Zend_Registry::get('cfg');
        return new ConnectionPool($cfg['cassandra']['db']['keyspace']);
    }

}