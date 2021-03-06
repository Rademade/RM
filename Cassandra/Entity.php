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

    use RM_Cassandra_Trait_EntityTypeOfAttributes;

    const TABLE_NAME = '';

    const ID_NAME = 'id';
    const ID_TYPE = self::AS_UUID;

    const ENTITY_ID_ATTRIBUTE_PREFIX = 'id';
    const ENTITY_LOOKUP_METHOD_PREFIX = 'find';

    const AS_IS = ValueDecorator::AS_IS;
    const AS_STRING = ValueDecorator::AS_STRING;
    const AS_INTEGER = ValueDecorator::AS_INTEGER;
    const AS_UUID = ValueDecorator::AS_UUID;
    const AS_BOOLEAN = ValueDecorator::AS_BOOLEAN;

    protected $_id;
    protected $_attributes;
    protected $_aggregations;
    protected $_dirty;

    public function __construct(array $attributes = array()) {
        $this->_id = $this->typeCast(rm_isset($attributes, static::ID_NAME), static::ID_TYPE);
        $this->_attributes = $attributes;
        unset($this->_attributes[static::ID_NAME]);
        $this->_dirty = !empty($this->_id);
    }

    public static function getSelect() {
        return static::enhanceSelect(new Select(static::TABLE_NAME));
    }

    public static function enhanceSelect(Select $select) {
        return $select;
    }

    public static function byId($id) {
        if (!$id) return;
        try {
            $select = static::getSelect()->one();
            $select->where()->valueOf(static::ID_NAME)->equalsTo($id)->treatedAs(static::ID_TYPE);
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

        return $this->typeCastAttribute($attrName, $default);
    }

    public function set($attrName, $value) {
        if ($value instanceof RM_Interface_Identifiable) {
            $this->aggregations()[$attrName] = $value;
            $attrName = static::ENTITY_ID_ATTRIBUTE_PREFIX . ucfirst($attrName);
            $value = $value->getId();
        }
        $this->attributes()[$attrName] = $this->typeCastAttribute($attrName, $value);
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

    public function remove() {
        if ($this->_id) {
            $delete = new RM_Cassandra_Query_Delete(static::TABLE_NAME);
            $delete->where()->valueOf(static::ID_NAME)->equalsTo($this->getId())->treatedAs(static::ID_TYPE);
            Cql30::exec($delete);
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
                $attributes[static::ENTITY_ID_ATTRIBUTE_PREFIX . ucfirst($entityName)] = $entity->getId();
            }
        }
    }

    protected function __buildInsertQuery() {
        $insert = new Insert(static::TABLE_NAME);

        $insert->value($this->getId())->namedAs(static::ID_NAME)->treatedAs(static::ID_TYPE);

        foreach (static::$attributesDefinition as $attrName => $treatAs) {
            $insert->value($this->get($attrName))->namedAs($attrName)->treatedAs($treatAs);
        }

        return $insert;
    }

    protected function __lookupAggregation($entityAttribute) {
        $aggregations = &$this->_aggregations;
        if ($aggregations && isset($aggregations[$entityAttribute])) {
            return $aggregations[$entityAttribute];
        }

        $entityName = ucfirst($entityAttribute);
        $idAttribute = static::ENTITY_ID_ATTRIBUTE_PREFIX . $entityName;
        $attributes = &$this->attributes();

        if (isset($attributes[$idAttribute])) {
            $lookupMethod = static::ENTITY_LOOKUP_METHOD_PREFIX . $entityName;
            if (method_exists($this, $lookupMethod)) {
                $e = $this->{$lookupMethod}($attributes[$idAttribute]);
                return $this->aggregations()[$entityAttribute] = $e;
            }
        }
    }

}