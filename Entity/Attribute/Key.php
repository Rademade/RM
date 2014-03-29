<?php
class RM_Entity_Attribute_Key {

    private $_value;

    public function __construct($value) {
        $this->_value = $value;
    }

    public function getValue() {
        return $this->_value;
    }

}