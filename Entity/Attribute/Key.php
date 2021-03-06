<?php
class RM_Entity_Attribute_Key {

    private $_name;
    private $_value;

    public function __construct($name, &$value) {
        $this->_name = $name;
        $this->_value = &$value;
    }
    
    public function setValue($value) {
        $this->_value = $value;
    }

    public function getFieldName() {
        return $this->_name;
    }
    
    public function getValue() {
        return $this->_value;
    }

}