<?php
class RM_Entity_Storage {

	private static $_self;

	/**
	 * @var RM_Entity_Attribute_Properties[]
	 */
	private $_properties;
	/**
	 * @var RM_Entity_Attribute_Properties
	 */
	private $_keyProperties;

	private $_fields;

	private $_dataStorage = array();
	/**
	 * @var RM_Entity_Worker_Cache
	 */
	private $_cacher;

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
	public function getProperties() {
		return $this->_properties;
	}

	public function &getKeyProperties() {
		if (!($this->_keyProperties instanceof RM_Entity_Attribute_Properties)) {
			foreach ($this->_properties as $attributeProperties) {
				if ($attributeProperties->isKey()) {
					$this->_keyProperties = $attributeProperties;
				}
			}
		}
		return $this->_keyProperties;
	}

	public function getFieldNames() {
		if (!is_array($this->_fields)) {
			$this->_fields = array();
			foreach ($this->_properties as $attribute) {
				array_push($this->_fields, $attribute->getFieldName());
			}
		}
		return $this->_fields;
	}

	public function setData($data, $key){
		$this->_dataStorage[$key] = $data;
	}

	public function getData($key) {
		return (isset($this->_dataStorage[$key])) ? $this->_dataStorage[$key] : null;
	}

	/**
	 * @param $className
	 * @return RM_Entity_Worker_Cache
	 */
	public function getCacher($className) {
		if (!($this->_cacher instanceof RM_Entity_Worker_Cache)) {
			$this->_cacher = new RM_Entity_Worker_Cache( $className );
		}
		return $this->_cacher;
	}

	public function parse($properties) {
		$this->_properties = array();
		foreach ($properties as $attribute => $property) {
			$this->_properties[] = new RM_Entity_Attribute_Properties(
				$attribute,
				$property
			);
		}
	}

}