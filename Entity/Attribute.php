<?php
class RM_Entity_Attribute {

	private $_name;
	private $_id;
	private $_field;
	private $_type;
	private $_value;
	private $_default;

	public function __construct($name, array $settings) {
		$this->_name = $name;
		$this->_id = isset($settings['id']) && $settings['id'];
		$this->_type = $settings['type'];
		$this->_field = isset($settings[ 'field' ]) ? $settings['field'] : $name;
		$this->_default = isset($settings['default']) ? $settings['default'] : '';
	}

	public function getName() {
		return $this->_name;
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

	protected function _parseValue($value) {
		switch ($this->getType()) {
			case 'int':
				return (int)$value;
			case 'string':
				return (string)$value;
			default:
				throw new Exception("Unknow {$this->getType()} value type");
		}
	}

	public function setValue($value) {
		$this->_value  = $this->_parseValue( $value );
	}

	public function getValue() {
		if (is_null($this->_value)) {
			$this->setValue( $this->getDefault() );
		}
		return $this->_value;
	}

	public function getDefault(){
		return $this->_default;
	}
	
}