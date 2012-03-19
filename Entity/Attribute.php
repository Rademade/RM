<?php
class RM_Entity_Attribute {

	/**
	 * @var RM_Entity_Attribute_Properties
	 */
	private $_attributeProperties;
	private $_value;

	public function __construct(RM_Entity_Attribute_Properties &$properties) {
		$this->_attributeProperties = $properties;
	}

	/**
	 * @return RM_Entity_Attribute_Properties
	 */
	private function _getProperties() {
		return $this->_attributeProperties;
	}

	protected function _parseValue($value) {
		switch ($this->_getProperties()->getType()) {
			case 'int':
				return (int)$value;
			case 'string':
				return (string)$value;
			case 'decimal':
			case 'float':
				return $value - 0.0;
			default:
				throw new Exception("Unknow '{$this->_getProperties()->getFieldName()}' value type");
		}
	}

	public function getAttributeName() {
		return $this->_getProperties()->getName();
	}

	public function getFieldName() {
		return $this->_getProperties()->getFieldName();
	}

	public function setValue($value) {
		$this->_value  = $this->_parseValue( $value );
	}

	public function getValue() {
		if (is_null($this->_value)) {
			$this->setValue( $this->_getProperties()->getDefault() );
		}
		return $this->_value;
	}

}