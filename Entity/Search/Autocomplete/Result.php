<?php
class RM_Entity_Search_Autocomplete_Result {

    private $_data;

    private $_type;

    private $_description;

    const FIELD_NAME = 'autocompleateName';

    public function __construct(stdClass $data) {
        $this->_data = $data;
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
        return $this->_data->{ self::FIELD_NAME };
    }

    public function __toArray() {
        return array(
            'value' => $this->getValue(),
            'type' => $this->getType(),
            'desc' => $this->getDescription()
        );
    }

}