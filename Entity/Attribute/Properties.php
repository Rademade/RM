<?php
class RM_Entity_Attribute_Properties {

	private $_name;
	private $_id;
	private $_field;
	private $_isColumn;
	private $_type;
	private $_default;

	public function __construct($name, array $settings) {
		$this->_name = $name;
		$this->_id = isset($settings['id']) && $settings['id'];
		$this->_type = $settings['type'];
		$this->_field = isset($settings[ 'field' ]) ? $settings['field'] : $name;
		$this->_isColumn = !(isset($settings[ 'column' ]) && $settings[ 'column' ] === false);
		$this->_default = isset($settings['default']) ? $settings['default'] : '';
	}

	public function getName() {
		return $this->_name;
	}

	public function isColumn() {
		return $this->_isColumn;
	}

	public function getFieldName() {
		return $this->_field;
	}

	public function getType() {
		return $this->_type;
	}

	public function isKey() {
		return $this->_id === true;
	}

	public function getDefault(){
		return $this->_default;
	}

}
