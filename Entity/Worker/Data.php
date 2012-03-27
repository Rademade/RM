<?php
class RM_Entity_Worker_Data
	implements
		Serializable {

	private $_callClassName;

	private $_table;

	private $_key;
	/**
	 * @var RM_Entity_Attribute[]
	 */
	private $_attributes = array();
	/**
	 * @var RM_Entity_Attribute_Properties[]
	 */
	private $_properties = array();

	private $_changes = array();

	/**
	 * @param string $className
	 * @param stdClass $data
	 */
	public function __construct($className, stdClass $data) {
		$this->_callClassName = $className;
		$this->_initProperties( $className );
		$this->_initEntity( $data );
	}

	private function _initProperties($className) {
		$this->_table = $className::TABLE_NAME;
		$this->_properties = call_user_func(
            $className . '::getAttributesProperties'
        );
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	private function _initEntity($data) {
		$c = sizeof($this->_properties);
		for ($i = 0; $i < $c; ++$i) {
			$attr = new RM_Entity_Attribute( $this->_properties[$i] );//create attribute
			$name = $attr->getFieldName();
			if (isset( $data->$name )) {//set attribute value
				$attr->setValue( $data->$name );
			}
			$this->_attributes[ $attr->getName() ] = $attr;//set attribute to attributes array
			if ($this->_properties[$i]->isKey()) {//set key attribute
				$this->_key = $attr->getName();
			}
			unset($attr);
		}
		if (is_null($this->_key)) {
			throw new Exception('Key attribute not defined');
		}
	}

	private function &_getKey() {
		return $this->_attributes[ $this->_key ];
	}

	private function _isExistAttribute( $name ) {
		return isset($this->_attributes[ $name ]);
	}

	public function getValue($name) {
		if ($this->_isExistAttribute( $name )) {
			return $this->_attributes[ $name ]->getValue();
		} else {
			return null;
		}
	}

	public function setValue($name, $value) {
		if ($this->_isExistAttribute( $name )) {
			if ($this->_attributes{$name} !== $value) {
				$this->_changes[
					$this->_attributes[ $name ]->getFieldName()
				] = $value;
	 			$this->_attributes[ $name ]->setValue( $value );
				return true;
			}
			return false;
		} else {
			return null;
		}
	}

	public function save() {
		if ($this->_getKey()->getValue() == 0) {
			RM_Entity::getDb()->insert(
				$this->_table,
				$this->_getInsertData()
			);
			$this->_getKey()->setValue( RM_Entity::getDb()->lastInsertId() );
			$this->_changes = array();
			return true;
		} else {
			if (!empty($this->_changes)) {
				RM_Entity::getDb()->update(
					$this->_table,
					$this->_changes,
					$this->_getKey()->getFieldName() . ' = ' . $this->_getKey()->getValue()
				);
				$this->_changes = array();
				return true;
			}
		}
		return false;
	}

	private function _getInsertData() {
		$data = array();
		foreach ($this->_attributes as $attribute) {
			$data[ $attribute->getFieldName() ] = $attribute->getValue();
		}
		return $data;
	}

	public function serialize() {
		$values = array();
		foreach ($this->_attributes as $attribute) {
			$values[ $attribute->getName() ] = $attribute->getValue();
		}
		return json_encode( array(
            'c' => $this->_callClassName,
			'v' => $values
		));
	}

	public function unserialize($serializedData) {
		$data = json_decode( $serializedData );
		$this->_callClassName = $data->c;
		$this->_initProperties( $data->c );
		$this->_initEntity( $data->v );
	}

}