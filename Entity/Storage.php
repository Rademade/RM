<?php
class RM_Entity_Storage {

	private static $_self;

	/**
	 * @var RM_Entity_Attribute_Properties[]
	 */
	private $_attributeProperties;
	/**
	 * @var RM_Entity_Attribute_Properties
	 */
	private $_keyAttributeProperties;

	private $_fields;

	private $_entities = array();

	private function __construct() {}

	/**
	 * @static
	 * @param $className
	 * @return RM_Entity_Storage
	 */
	public static function &getInstance($className) {
		if (!isset(self::$_self[ $className ])) {
			self::$_self[ $className ] = new self();
		}
		return self::$_self[ $className ];
	}

	/**
	 * @return RM_Entity_Attribute_Properties[]
	 */
	public function &getAttributeProperties() {
		return $this->_attributeProperties;
	}

	public function &getKeyAttributeProperties() {
		if (!($this->_keyAttributeProperties instanceof RM_Entity_Attribute_Properties)) {
			foreach ($this->getAttributeProperties() as $attributeProperties) {
				if ($attributeProperties->isKey()) {
					$this->_keyAttributeProperties = $attributeProperties;
				}
			}
			if (!($this->_keyAttributeProperties instanceof RM_Entity_Attribute_Properties)) {
				throw new Exception('Key attribute undefined');
			}
		}
		return $this->_keyAttributeProperties;
	}

	public function &getFieldNames() {
		if (!is_array($this->_fields)) {
			$this->_fields = array();
			foreach ($this->getAttributeProperties() as $attribute) {
				array_push($this->_fields, $attribute->getFieldName());
			}
		}
		return $this->_fields;
	}

	public function setEntity($key, RM_Entity &$entity){
		$this->_entities[$key] = $entity;
	}

	public function getEntity($key) {
		return (isset($this->_entities[$key])) ? $this->_entities[$key] : null;
	}

	public function parse($properties) {
		$this->_attributeProperties = array();
		foreach ($properties as $attribute => $property) {
			$attributeProperties = new RM_Entity_Attribute_Properties($attribute, $property);
			$this->_attributeProperties[ $attributeProperties->getFieldName() ] = $attributeProperties;
		}
	}

}