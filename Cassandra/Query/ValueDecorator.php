<?php
/**
 * Class RM_Cassandra_Query_ValueDecorator
 * @link http://www.datastax.com/documentation/cql/3.0/cql/cql_reference/cql_data_types_c.html
 */
class RM_Cassandra_Query_ValueDecorator {

    const AS_IS = 0;
    const AS_STRING = 1;
    const AS_INTEGER = 2;
    const AS_UUID = 3;
    const AS_BOOLEAN = 4;

    protected static $_instance;

    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * From CQL 3.0:
     * Enclose ASCII text, timestamp, and inet values in single quotation marks
     * Enclose names of a keyspace, table, or column in double quotation marks.
     */
    public function decorate($value, $as = null) {
        if (null === $as) {
            $as = self::AS_IS;
        }
        if (is_array($value)) {
            return $this->__decoratePlural($value, $as);
        } else {
            return $this->__decorateSingular($value, $as);
        }
    }

    protected function __decorateSingular($value, $as) {
        switch ((int)$as) {
            case self::AS_INTEGER:
                return (int)$value;
            case self::AS_STRING:
                return '\'' . addslashes((string)$value) . '\'';
            case self::AS_UUID:
                return (string)$value;
            case self::AS_BOOLEAN:
                return $value ? 'true' : 'false';
        }
        if (is_string($value)) {
            return $this->__decorateSingular($value, self::AS_STRING);
        }
        if (is_integer($value)) {
            return $this->__decorateSingular($value, self::AS_INTEGER);
        }
        return $value;
    }

    /**
     * @param array $values
     * @param int   $as
     * @return string
     *
     * // RM_TODO decoration of map alongside set
     */
    protected function __decoratePlural(array $values, $as) {
        $code = '(';
        $i = 0;
        $length = sizeof($values);
        while ($i < $length) {
            $code .= $this->__decorateSingular($values[$i], $as);
            if (++$i < $length)
                $code .= ',';
        }
        return $code . ')';
    }

}