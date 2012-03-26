<?php
class RM_Entity_Attribute {

	/**
	 * @var RM_Entity_Attribute_Properties
	 */
	private $_attributeProperties;
	private $_value;

	public function __construct(RM_Entity_Attribute_Properties $properties) {
		$this->_attributeProperties = $properties;
	}

	protected function _parseValue($value) {
		switch ($this->_attributeProperties->getType()) {
			case 'int':
				return (int)$value;
			case 'string':
				return (string)$value;
			case 'decimal':
			case 'float':
				return $value - 0.0;
			default:
				throw new Exception("Unknow '{$this->_attributeProperties->getFieldName()}' value type");
		}
	}

	public function getAttributeName() {
		return $this->_attributeProperties->getName();
	}

	public function getFieldName() {
		return $this->_attributeProperties->getFieldName();
	}

	public function setValue($value) {
		$this->_value  = $this->_parseValue( $value );
	}

	public function getValue() {
		if (is_null($this->_value)) {
			$this->setValue( $this->_attributeProperties->getDefault() );
		}
		return $this->_value;
	}

}