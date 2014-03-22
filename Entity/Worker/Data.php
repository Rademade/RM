<?php
class RM_Entity_Worker_Data
    implements
        Serializable {

    /**
     * @var RM_Entity
     */
    private $_calledClassName;

    private $_table;
    private $_key;
    /**
     * @var RM_Entity_Attribute[]
     */
    private $_attributes = array();
    /**
     * @var RM_Entity_Attribute_Properties[]
     */
    private $_properties = array();
    private $_changes = array();

    /**
     * @param string   $className
     * @param stdClass $data
     */
    public function __construct($className, stdClass $data) {
        $this->_calledClassName = $className;
        $this->_initProperties($className);
        $this->_initEntity($data);
    }

    public function &_getKey() {
        return $this->_attributes[$this->_key];
    }

    /**
     * @return stdClass
     */
    public function getAllData() {
        $data = array();
        foreach ($this->_attributes as $name => $value) {
            $data[$name] = $value->getValue();
        }
        return (object)$data;
    }

    public function isChanged($fieldName) {
        return isset($this->_changes[$fieldName]);
    }

    public function getValue($name) {
        if ($this->_isExistAttribute($name)) {
            return $this->_attributes[$name]->getValue();
        }
        return null;
    }

    public function setValue($name, $value) {
        if ($this->_isExistAttribute($name)) {
            if ($this->_attributes[$name]->getValue() !== $value) {
                $this->_changes[$this->_attributes[$name]->getFieldName()] = $value;
                $this->_attributes[$name]->setValue($value);
                return true;
            }
            return false;
        }
        return null;
    }

    public function isInserted() {
        $inserted = ($this->_getKey()->getValue() !== 0);
        if ($inserted && !$this->_getKey()->isAutoIncrement()) {
            $entity = call_user_func( //TODO refactor
                $this->_calledClassName . '::getById',
                $this->_getKey()->getValue()
            );
            $inserted = ($entity instanceof RM_Entity && $entity->getId() != 0);
        }
        return $inserted;
    }

    public function save() {
        /* @var RM_Entity $className */
        $className = $this->_calledClassName;
        if (!$this->isInserted()) {
            $className::getDb()->insert(
                $this->_table,
                $this->_getInsertData()
            );
            if ($this->_getKey()->isAutoIncrement()) {
                $this->_getKey()->setValue($className::getDb()->lastInsertId());
            }
            $this->_changes = array();
            return true;
        } else {
            if (!empty($this->_changes)) {
                $className::getDb()->update(
                    $this->_table,
                    $this->_changes,
                    $this->_getKey()->getFieldName() . ' = ' . $this->_getKey()->getValue()
                );
                $this->_changes = array();
                return true;
            }
        }
        return false;
    }

    public function remove() {
        /* @var RM_Entity $className */
        $className = $this->_calledClassName;
        $className::getDb()->delete(
            $this->_table,
            $this->_getKey()->getFieldName() . ' = ' . $this->_getKey()->getValue()
        );
    }

    public function serialize() {
        $values = array();
        foreach ($this->_attributes as $attribute) {
            $values[$attribute->getFieldName()] = $attribute->getValue();
        }
        return serialize(array(
            'c' => $this->_calledClassName,
            'v' => $values
        ));
    }

    public function unserialize($serializedData) {
        $data = unserialize($serializedData);
        $this->_calledClassName = $data['c'];
        $this->_initProperties($data['c']);
        $this->_initEntity((object)$data['v']);
    }

    private function _initProperties($className) {
        $this->_table = $className::TABLE_NAME;
        $this->_properties = call_user_func( //TODO refactor
            $className . '::getAttributesProperties'
        );
        if (empty($this->_properties)) {
            throw new Exception('Entity properties are not defined');
        }
    }

    /**
     * @param $data
     * @throws Exception
     */
    private function _initEntity($data) {
        $c = sizeof($this->_properties);
        for ($i = 0; $i < $c; ++$i) {
            $attr = new RM_Entity_Attribute($this->_properties[$i]); //create attribute
            $name = $attr->getFieldName();
            if (isset($data->$name)) { //set attribute value
                $attr->setValue($data->$name);
            }
            $this->_attributes[$attr->getName()] = $attr; //set attribute to attributes array
            if ($this->_properties[$i]->isKey()) { //set key attribute
                $this->_key = $attr->getName();
            }
            unset($attr);
        }
        if (is_null($this->_key)) {
            throw new Exception('Key attribute is not defined');
        }
    }

    private function _isExistAttribute($name) {
        return isset($this->_attributes[$name]);
    }

    private function _getInsertData() {
        $data = array();
        foreach ($this->_attributes as $attribute) {
            $data[$attribute->getFieldName()] = $attribute->getValue();
        }
        return $data;
    }

}