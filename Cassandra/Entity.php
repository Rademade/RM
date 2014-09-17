<?php
require_once LIBRARY_PATH . '/Uuid/Uuid.php';

use Rhumsaa\Uuid\Console\Exception;
use RM_Interface_Identifiable as Identifiable;
use RM_Interface_Savable as Savable;
use RM_Cassandra_Query_ValueDecorator as ValueDecorator;
use RM_Cassandra_Query_Select as Select;
use RM_Cassandra_Query_Insert as Insert;
use Rhumsaa\Uuid\Uuid;

abstract class RM_Cassandra_Entity
    implements
        Identifiable,
        Savable {

    const TABLE_NAME = '';

    protected static $_asUuid = '';
    protected static $_asInteger = '';

    protected $_id;
    protected $_attributes;
    protected $_aggregations;
    protected $_dirty;

    public function __construct(array $attributes = array()) {
        $this->_id = isset($attributes['id']) ? $attributes['id'] : null;
        $this->_attributes = $attributes;
        unset($this->_attributes['id']);
        $this->_dirty = false;
    }

    public static function getSelect() {
        return static::enhanceSelect(new Select(static::TABLE_NAME));
    }

    public static function enhanceSelect(Select $select) {
        return $select;
    }

    public static function byId($id) {
        try {
            $select = static::getSelect()->one();
            $select->where()->valueOf('id')->equalsTo($id)->asUuid();
            $data = RM_Cassandra_Cql::exec($select);
            if (isset($data[0])) return static::buildOne($data[0]);
        } catch (Exception $e) {}
    }

    public static function findOne(array $conditions) {
        $select = static::getSelect()->one();
        $w = $select->where();
        foreach ($conditions as $attrName => $attrValue) {
            $w->valueOf($attrName)->equalsTo($attrValue);
        }
        $data = RM_Cassandra_Cql::exec($select);
        if (isset($data[0])) return static::buildOne($data[0]);
    }

    public static function buildOne($data) {
        return new static($data);
    }

    public static function buildMany(array $data) {
        return array_map(function($data) {
            return static::buildOne($data);
        }, $data);
    }

    public function getId() {
        if (!$this->_id) {
            $this->_id = Uuid::uuid1();
        }
        return $this->_id;
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
            $attrName = 'id' . ucfirst($attrName);
            $value = $value->getId();
        }
        $this->attributes()[$attrName] = $this->__typeCast($attrName, $value);
        $this->_dirty = true;
        return $this;
    }

    public function save() {
        if ($this->_dirty) {
            $this->__saveAggregations();

            $insert = new Insert(static::TABLE_NAME);
            $insert->value($this->getId())->namedAs('id')->treatedAs(ValueDecorator::AS_UUID);
            foreach ($this->attributes() as $attrName => $attrValue) {
                $asUuid = false !== strpos(static::$_asUuid, $attrName);
                $insert->value($attrValue)->namedAs($attrName)->treatedAs($asUuid ? ValueDecorator::AS_UUID : null);
            }

            RM_Cassandra_Cql::exec($insert);

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

    protected function __typeCast($attrName, $attrValue) {
        $treatedAsInt = false !== strpos(static::$_asInteger, $attrName);
        return $treatedAsInt ? (int)$attrValue : (string)$attrValue;
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

}