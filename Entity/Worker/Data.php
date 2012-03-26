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
	private $_attributeProperties = array();

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
		$this->_attributeProperties = call_user_func(
            $className . '::getAttributesProperties'
        );
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	private function _initEntity($data) {
		$c = sizeof($this->_attributeProperties);
		for ($i = 0; $i < $c; ++$i) {
			$attribute = new RM_Entity_Attribute( $this->_attributeProperties[$i] );//create attribute
			$name = $attribute->getFieldName();
			if (isset( $data->{$name} )) {//set attribute value
				$attribute->setValue( $data->{$name} );
			}
			$this->_attributes[ $attribute->getAttributeName() ] = $attribute;//set attribute to attributes array
			if ($this->_attributeProperties[$i]->isKey()) {//set key attribute
				$this->_key = $attribute->getAttributeName();
			}
		}
		if (is_null($this->_key)) {
			throw new Exception('Key attribute not defined');
		}
	}

	private function &_getKeyAttribute() {
		return $this->_attributes[ $this->_key ];
	}

	private function _existAttribute( $name ) {
		return isset($this->_attributes[ $name ]);
	}

	public function getValue($name) {
		if ($this->_existAttribute( $name )) {
			return $this->_attributes[ $name ]->getValue();
		} else {
			return null;
		}
	}

	public function setValue($name, $value) {
		if ($this->_existAttribute( $name )) {
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
		if ($this->_getKeyAttribute()->getValue() == 0) {
			RM_Entity::getDb()->insert(
				$this->_table,
				$this->_getInsertData()
			);
			$this->_getKeyAttribute()->setValue( RM_Entity::getDb()->lastInsertId() );
			$this->_changes = array();
			return true;
		} else {
			if (!empty($this->_changes)) {
				RM_Entity::getDb()->update(
					$this->_table,
					$this->_changes,
					$this->_getKeyAttribute()->getFieldName() . ' = ' . $this->_getKeyAttribute()->getValue()
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
			$values[ $attribute->getAttributeName() ] = $attribute->getValue();
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