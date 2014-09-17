<?php
use RM_Cassandra_Query_Select as QuerySelect;

abstract class RM_Cassandra_Search_Repository {

    abstract public function getEntityClassName();

    public function findOne(QuerySelect $select, $bindings = []) {
        $select->one();
        $data = $this->__execQuery($select, $bindings);
        if (isset($data[0])) {
            $className = $this->getEntityClassName();
            return $className::buildOne($data[0]);
        }
    }

    public function findMany(QuerySelect $select, $bindings = []) {
        $data = $this->__execQuery($select, $bindings);
        $className = $this->getEntityClassName();
        return array_map(function($data) use ($className) {
            return $className::buildOne($data);
        }, $data);
    }

    public function getCount(QuerySelect $select, $bindings = []) {
        $select->columns('COUNT(*)');
        return (int)$this->__execQuery($select, $bindings)[0]['count'];
    }

    /**
     * @return QuerySelect
     */
    public function getSelect() {
        $className = $this->getEntityClassName();
        return $className::getSelect();
    }

    protected function __execQuery($query, $bindings) {
        return RM_Cassandra_Cql::exec($query, $bindings);
    }

}