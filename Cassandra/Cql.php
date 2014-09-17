<?php
class RM_Cassandra_Cql {

    /**
     * @param string $query
     * @param array $bindings
     *
     * @return array
     */
    public static function exec($query, $bindings = []) {
        return RM_Cassandra_Db::get()->query($query, $bindings);
    }

}