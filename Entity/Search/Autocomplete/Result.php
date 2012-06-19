<?php
abstract class RM_Entity_Search_Autocomplete_Result {

    private $_data;

    const FIELD_NAME = 'autocompleateName';

    public function __construct(stdClass $data) {
        $this->_data = $data;
    }

    public function getName() {
        return $this->_data->{ self::FIELD_NAME };
    }

    abstract public function getType();

    public function __toArray() {
        return array(
            'value' => $this->getName(),
            'type' => $this->getType()
        );
    }

}