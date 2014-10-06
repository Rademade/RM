<?php
require_once LIBRARY_PATH . '/Uuid/Uuid.php';

use Rhumsaa\Uuid\Console\Exception;
use RM_Interface_Identifiable as Identifiable;
use RM_Interface_Savable as Savable;
use RM_Cassandra_Query_ValueDecorator as ValueDecorator;
use RM_Cassandra_Query_Select as Select;
use RM_Cassandra_Query_Insert as Insert;
use Rhumsaa\Uuid\Uuid;
use RM_Cassandra_Cql as Cql30;

abstract class RM_Cassandra_Entity
    implements
        Identifiable,
        Savable {

    const TABLE_NAME = '';
    const ID_ATTRIBUTE_NAME = 'id';
    const AGGREGATED_ENTITY_ID_ATTRIBUTE_PREFIX = 'id';
    const AGGREGATED_ENTITY_LOOKUP_METHOD_PREFIX = 'find';

    protected static $_asUuid;
    protected static $_asInteger;
    protected static $_asBoolean;

    protected $_id;
    protected $_attributes;
    protected $_aggregations;
    protected $_dirty;

    public function __construct(array $attributes = array()) {
        $this->_id = rm_isset($attributes, static::ID_ATTRIBUTE_NAME);
        $this->_attributes = $attributes;
        unset($this->_attributes[static::ID_ATTRIBUTE_NAME]);
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
            $data = Cql30::exec($select);
            if (isset($data[0])) return static::buildOne($data[0]);
        } catch (Exception $e) {}
    }

    public static function findOne(array $conditions) {
        $select = static::getSelect()->one();
        $w = $select->where();
        foreach ($conditions as $attrName => $attrValue) {
            $w->valueOf($attrName)->equalsTo($attrValue);
        }
        $data = Cql30::exec($select);
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
            $this->aggregations()[$attrName] = $value;
            $attrName = static::AGGREGATED_ENTITY_ID_ATTRIBUTE_PREFIX . ucfirst($attrName);
            $value = $value->getId();
        }
        $this->attributes()[$attrName] = $this->__typeCast($attrName, $value);
        $this->_dirty = true;
        return $this;
    }

    public function save() {
        if ($this->_dirty) {
            $this->__saveAggregations();
            $insert = $this->__buildInsertQuery();
            Cql30::exec($insert);
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

    protected function __buildInsertQuery() {
        $insert = new Insert(static::TABLE_NAME);
        $id = $this->getId();
        $idAttribute = static::ID_ATTRIBUTE_NAME;

        $insert->value($id)->namedAs($idAttribute)->treatedAs(ValueDecorator::AS_UUID);

        foreach ($this->attributes() as $attrName => $attrValue) {
            if ($this->__treatedAsUuid($attrName)) {
                $as = ValueDecorator::AS_UUID;
            } elseif ($this->__treatedAsBoolean($attrName)) {
                $as = ValueDecorator::AS_BOOLEAN;
            } else {
                $as = null;
            }

            $insert->value($attrValue)->namedAs($attrName)->treatedAs($as);
        }

        return $insert;
    }

    protected function __treatedAsUuid($attrName) {
        return is_array(static::$_asUuid) && in_array($attrName, static::$_asUuid);
    }

    protected function __treatedAsInteger($attrName) {
        return is_array(static::$_asInteger) && in_array($attrName, static::$_asInteger);
    }

    protected function __treatedAsBoolean($attrName) {
        return is_array(static::$_asBoolean) && in_array($attrName, static::$_asBoolean);
    }

    protected function __typeCast($attrName, $attrValue) {
        return $this->__treatedAsInteger($attrName) ? (int)$attrValue : $attrValue;
    }

    protected function __saveAggregations() {
        if (!$this->_aggregations) return;
        $attributes = &$this->attributes();
        foreach ($this->_aggregations as $entityName => $entity) {
            if ($entity instanceof Identifiable) {
                if ($entity instanceof Savable) {
                    $entity->save();
                }
                $attributes[static::AGGREGATED_ENTITY_ID_ATTRIBUTE_PREFIX . ucfirst($entityName)] = $entity->getId();
            }
        }
    }

    protected function __lookupAggregation($entityAttribute) {
        $aggregations = &$this->_aggregations;
        if ($aggregations && isset($aggregations[$entityAttribute])) {
            return $aggregations[$entityAttribute];
        }

        $entityName = ucfirst($entityAttribute);
        $idAttribute = static::AGGREGATED_ENTITY_ID_ATTRIBUTE_PREFIX . $entityName;
        $attributes = &$this->attributes();

        if (isset($attributes[$idAttribute])) {
            $lookupMethod = static::AGGREGATED_ENTITY_LOOKUP_METHOD_PREFIX . $entityName;
            if (method_exists($this, $lookupMethod)) {
                $e = $this->{$lookupMethod}($attributes[$idAttribute]);
                return $this->aggregations()[$entityAttribute] = $e;
            }
        }
    }

}