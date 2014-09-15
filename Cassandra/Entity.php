<?php

RM_Cassandra_Loader::load();

use phpcassa\ColumnFamily;
use phpcassa\UUID;
use RM_Interface_Identifiable as Identifiable;
use RM_Interface_Savable as Savable;

abstract class RM_Cassandra_Entity
    implements
        Identifiable,
        Savable {

    const TABLE_NAME = '';

    /**
     * @var ColumnFamily[]
     */
    protected static $_tables = [];

    protected $_key;
    protected $_attributes;
    protected $_aggregations;
    protected $_dirty;

    public function __construct($key = null, array $attributes = array()) {
        $this->_key = $key;
        $this->_attributes = $attributes;
        $this->_dirty = false;
    }

    public static function getConnection() {
        return RM_Cassandra_Connection::connect();
    }

    /**
     * @return ColumnFamily
     */
    public static function getTable() {
        $class = get_called_class();
        if (!isset(static::$_tables[$class])) {
            static::$_tables[$class] = static::__openTable();
        }
        return static::$_tables[$class];
    }

    /**
     * @return ColumnFamily
     * @throws Exception
     */
    protected static function __openTable() {
        if (!static::TABLE_NAME) {
            throw new Exception('Table name was not defined');
        }
        return new ColumnFamily(static::getConnection(), static::TABLE_NAME);
    }

    public static function byKey($key) {
        try {
            $attributes = static::getTable()->get($key);
        } catch (Exception $e) {
            return null;
        }
        return new static($key, $attributes);
    }

    public static function getList() {
        $list = array();
        $range = static::getTable()->get_range();
        foreach ($range as $key => $attributes) {
            $list[] = new static($key, $attributes);
        }
        return $list;
    }

    public function key() {
        return $this->_key ?: $this->_key = (string)UUID::uuid1();
    }

    public function getId() {
        return $this->key();
    }

    public function &attributes() {
        return $this->_attributes;
    }

    public function &aggregations() {
        if (!$this->_aggregations) {
            $this->_aggregations = [];
        }
        return $this->_aggregations;
    }

    public function get($attrName, $default = null) {
        $attributes = &$this->attributes();

        if (isset($attributes[$attrName])) {
            return $attributes[$attrName];
        }

        if ($e = $this->__lookupAggregation($attrName)) {
            return $e;
        }

        return $default;
    }

    public function set($attrName, $value) {
        if ($value instanceof RM_Interface_Identifiable) {
            if (!$this->_aggregations) {
                $this->_aggregations = [];
            }
            $this->_aggregations[$attrName] = $value;
            $this->attributes()['id' . ucfirst($attrName)] = $value->getId();
        } else {
            $this->attributes()[$attrName] = $value;
        }
        $this->_dirty = true;
        return $this;
    }

    public function save() {
        if ($this->_dirty) {
            $this->__saveAggregations();
            $this->__removeNullAttributes();
            static::getTable()->insert($this->key(), $this->attributes());
            $this->_dirty = false;
        }
        return $this;
    }

    public function __call($method, $args) {
        if (strlen($method) < 4) return $this->get($method);
        if ($method[3] !== ucfirst($method[3])) return $this->get($method);

        $task = substr($method, 0, 3);
        $attribute = lcfirst(substr($method, 3));

        array_unshift($args, $attribute);

        return call_user_func_array([$this, $task], $args);
    }

    protected function __saveAggregations() {
        if (!$this->_aggregations) return;
        $attributes = &$this->attributes();
        foreach ($this->_aggregations as $entityName => $entity) {
            if ($entity instanceof Identifiable) {
                if ($entity instanceof Savable) {
                    $entity->save();
                }
                $attributes['id' . ucfirst($entityName)] = $entity->getId();
            }
        }
    }

    protected function __lookupAggregation($entityAttribute) {
        $aggregations = &$this->_aggregations;
        if ($aggregations && isset($aggregations[$entityAttribute])) {
            return $aggregations[$entityAttribute];
        }

        $entityName = ucfirst($entityAttribute);
        $idAttribute = 'id' . $entityName;
        $attributes = &$this->attributes();

        if (isset($attributes[$idAttribute])) {
            $lookupMethod = 'find' . $entityName;
            if (method_exists($this, $lookupMethod)) {
                $e = $this->{$lookupMethod}($attributes[$idAttribute]);
                return $this->aggregations()[$entityAttribute] = $e;
            }
        }
    }

    protected function __removeNullAttributes() {
        $attributes = &$this->attributes();
        foreach ($attributes as $attrName => $attrValue) {
            if (null === $attrValue) {
                unset($attributes[$attrName]);
            }
        }
    }

}