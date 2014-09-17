<?php
class RM_Cassandra_Query_TableDecorator {

    protected static $_instance;

    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function decorateName($table) {
        return '"' . trim($table, '"') . '"';
    }

    public function decorateColumn($column) {
        return $this->decorateName($column);
    }

}