<?php
class RM_Entity_Worker_Data
    implements
        Serializable {

    /**
     * @var RM_Entity
     */
    private $_calledClassName;

    private $_table;
    private $_keyName;

    /**
     * @var array[]
     */
    private $_values = array();

    /**
     * @var RM_Entity_Attribute_Key
     */
    private $_key;

    /**
     * @var RM_Entity_Attribute_Properties[]
     */
    private $_properties = array();
    private $_changes = array();

    /**
     * @param RM_Entity|string $className
     * @param stdClass  $data
     */
    public function __construct($className, stdClass $data) {
        $this->_calledClassName = $className;
        $this->_initProperties($className);
        $this->_initEntity($data);
    }

    /**
     * @deprecated
     * @return RM_Entity_Attribute_Key
     */
    public function &_getKey() {
        if (!$this->_key instanceof RM_Entity_Attribute_Key) {
            $this->_key = new RM_Entity_Attribute_Key($this->_keyName, $this->_values[$this->_keyName]);
        }
        return $this->_key;
    }

    /**
     * @return stdClass
     */
    public function getAllData() {
        $data = array();
        foreach ($this->_values as $name => $value) {
            $data[$name] = $value;
        }
        return (object)$data;
    }

    public function isChanged($fieldName) {
        return isset($this->_changes[$fieldName]);
    }

    public function getValue($name) {
        if ($this->_isExistAttribute($name)) {
            return $this->_values[ $name ];
        }
        return null;
    }

    public function setValue($name, $value) {
        if ($this->_isExistAttribute($name)) {
            if ($this->_values[$name] !== $value) {
                $this->_changes[ $this->_attrNameToField( $name ) ] = $value;
                $this->_values[$name] = RM_Entity_Attribute_Properties::parseValue($this->_properties[$name], $value);
                return true;
            }
            return false;
        }
        return null;
    }

    public function isInserted() {
        $key = $this->_values[ $this->_keyName ];

        $inserted = ($key !== 0);
        if ($inserted && !$this->_properties[ $this->_keyName ]->isAutoIncrement()) {
            $entity = call_user_func( //TODO refactor
                $this->_calledClassName . '::getById',
                $key
            );
            $inserted = ($entity instanceof RM_Entity && $entity->getId() != 0);
        }
        return $inserted;
    }

    public function save() {
        /* @var RM_Entity $className */
        $className = $this->_calledClassName;
        if (!$this->isInserted()) {
            $this->_values = $this->_getPreSaveData();
            $className::getDb()->insert( $this->_table, $this->_getInsertData() );
            if ($this->_properties[ $this->_keyName ]->isAutoIncrement()) {
                $this->_values[ $this->_keyName ] = (int)$className::getDb()->lastInsertId();
            }
            $this->_changes = array();
            return true;
        } else {
            if (!empty($this->_changes)) {
                $className::getDb()->update(
                    $this->_table,
                    $this->_changes,
                    $this->_attrNameToField( $this->_keyName ) . ' = ' . $this->_values[ $this->_keyName ]
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
            $this->_attrNameToField( $this->_keyName ) . ' = ' . $this->_values[ $this->_keyName ]
        );
    }

    public function serialize() {
        return serialize(array(
            'c' => $this->_calledClassName,
            'v' => $this->_values
        ));
    }

    public function unserialize($serializedData) {
        $data = unserialize($serializedData);
        $this->_calledClassName = $data['c'];
        $this->_initProperties($data['c']);
        $this->_values = $data['v'];
    }

    /**
     * @param RM_Entity $className
     * @throws Exception
     */
    private function _initProperties($className) {
        $this->_table = $className::TABLE_NAME;
        $this->_properties = $className::getAttributesProperties();
        $this->_keyName = $className::getKeyAttributeName();

        if (empty($this->_properties)) {
            throw new Exception('Entity properties are not defined');
        }
    }

    /**
     * @param $data
     * @throws Exception
     */
    private function _initEntity($data) {
        foreach ($this->_properties as $property) {

            $fieldName = $property->getFieldName();

            $this->_values[ $property->getName() ] = RM_Entity_Attribute_Properties::parseValue(
                $property,
                isset($data->$fieldName) ? $data->$fieldName : $property->getDefault()
            );

            if ($property->isKey()) { //set key attribute
                $this->_keyName = $property->getName();
            }

        }
        if (is_null($this->_keyName)) {
            throw new Exception('Key attribute is not defined');
        }
    }

    private function _isExistAttribute($name) {
        return isset($this->_values[$name]);
    }

    private function _getPreSaveData() {
        $data = $this->_values;
        foreach ($this->_properties as $name => $property) {
            if (!$data[$name] && $property->getDefault()) {
                $data[ $name ] = $property->getDefault();
            }
        }
        return $data;
    }

    private function _getInsertData() {
        $data = array();
        foreach ($this->_values as $name => $value) {
            $data[ $this->_attrNameToField( $name ) ] = $value;
        }
        return $data;
    }

    private function _attrNameToField($name) {
        return $this->_properties[ $name ]->getFieldName();
    }

}