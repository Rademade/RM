<?php

RM_Cassandra_Loader::load();

use cassandra\Compression;
use cassandra\CqlResult;

class RM_Cassandra_Cql {

    private static $_taboo = [ '`', '"', '(', ')'];

    /**
     * @param Zend_Db_Select|string $query
     * @param int $compression
     *
     * @return CqlResult
     */
    public static function exec($query, $compression = Compression::NONE) {
        $pool = RM_Cassandra_Entity::getConnection();
        $raw  = $pool->get();
        return $raw->client->execute_cql_query(str_replace(self::$_taboo, '', $query), $compression);
    }

}