<?php
class RM_Entity_Search_Autocomplete_Result
    implements
        RM_Entity_Search_Result_Interface,
        JsonSerializable {

    private $_id;

    private $_value;

    private $_type;

    private $_description;

    public function __construct($value) {
        $this->_value = $value;
    }

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = intval($id);
    }

    public function setType($type) {
        $this->_type = $type;
    }

    public function getType() {
        return $this->_type;
    }

    public function setDescription($desc) {
        $this->_description = $desc;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getValue() {
        return $this->_value;
    }

    public function jsonSerialize() {
        return $this->__toArray();
    }

    public function __toArray() {
        return array(
            'id' => $this->getId(),
            'value' => $this->getValue(),
            'type' => $this->getType(),
            'desc' => $this->getDescription()
        );
    }

}