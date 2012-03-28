<?php
class RM_Entity_Attribute {

	/**
	 * @var RM_Entity_Attribute_Properties
	 */
	private $_properties;
	private $_value;

	public function __construct(RM_Entity_Attribute_Properties &$properties) {
		$this->_properties = $properties;
	}

	protected function _parseValue($value) {
		$type = $this->_properties->getType();
		if ($type === 'int') {
			return (int)$value;
		} elseif ($type === 'string') {
			return (string)$value;
		} elseif ($type === 'decimal' || $type === 'float') {
			return $value - 0.0;
		} else {
			throw new Exception("Unknow '{$this->_properties->getFieldName()}' value type");
		}
	}

	public function getName() {
		return $this->_properties->getName();
	}

	public function getFieldName() {
		return $this->_properties->getFieldName();
	}

	public function setValue($value) {
		$this->_value  = $this->_parseValue( $value );
	}

	public function getValue() {
		if (is_null($this->_value)) {
			$this->setValue( $this->_properties->getDefault() );
		}
		return $this->_value;
	}

}