<?php
abstract class RM_Entity {

	protected static $_table = '';

	protected static $_properties = array();

	/**
	 * @var RM_Entity_Attribute
	 */
	private $_key;
	/**
	 * @var RM_Entity_Attribute[]
	 */
	private $_attributes = array();

	private $_changes = array();

	public function __construct(stdClass $data) {
		foreach (self::getEntityAttributes() as $entityAttribute) {
			//TODO if field exist in attributes
			if (isset( $data->{ $entityAttribute->getFieldName() } )) {
				$entityAttribute->setValue( $data->{ $entityAttribute->getFieldName() } );
			}
			$this->_attributes[
				$entityAttribute->getName()
			] = $entityAttribute;
		}
		$this->_initId();
	}


	private function _initId() {
		foreach ($this->_attributes as &$attribute) {
			if ($attribute->isKey()) {
				$this->_key = $attribute;
				return;
			}
		}
		throw new Exception('Key attibute not setted');
	}

	public function existAttribute( $name ) {
		return isset($this->_attributes[ $name ]);
	}
	
	public function __get($name) {
		if ($this->existAttribute( $name )) {
			return $this->_attributes[ $name ]->getValue();
		} else {
			throw new Exception("Try to get unexpected attribute {$name}");
		}
	}

	public function __set($name, $value) {
		if ($this->existAttribute( $name )) {
			if ($this->{$name} !== $value) {
				$this->_changes[
					$this->_attributes[ $name ]->getFieldName()
				] = $value;
	 			$this->_attributes[ $name ]->setValue( $value );
			}
		} else {
			throw new Exception("Try to set unexpected attribute {$name}");
		}
	}

	public function save() {
		if ($this->_key->getValue() == 0) {
			self::getDb()->insert(
				static::$_table,
				$this->_getInsertData()
			);
			$this->_key->setValue( self::getDb()->lastInsertId() );
		} else {
			self::getDb()->update(
				static::$_table,
				$this->_getUpdateData(),
				$this->_key->getFieldName() . ' = ' . $this->_key->getValue()
			);
		}
		$this->_changes = array();
	}

	private function _getInsertData() {
		$data = array();
		foreach ($this->_attributes as $attribute) {
			$data[ $attribute->getFieldName() ] = $attribute->getValue();
		}
		return $data;
	}

	private function _getUpdateData() {
		return $this->_changes;
	}

	/**
	 * @static
	 * @return RM_Entity_Attribute[]
	 */
	private static function getEntityAttributes() {
		$_entityAttributes = array();
		foreach (static::$_properties as $attribute => $property) {
			$_entityAttribute = new RM_Entity_Attribute($attribute, $property);
			$_entityAttributes[ $_entityAttribute->getFieldName() ] = $_entityAttribute;
		}
		return $_entityAttributes;
	}

	protected static function _getDbAttributes() {
		$fields = array();
		foreach (self::getEntityAttributes() as $attribute) {
			array_push(
				$fields,
				$attribute->getFieldName()
			);
		}
		return $fields;
	}

	/**
	 * @static
	 * @return Zend_Db_Adapter_Abstract
	 */
	public static function getDb() {
		return Zend_Registry::get('db');
	}

	/**
	 * @static
	 * @return Zend_Db_Select
	 */
	protected static function _getSelect() {
		$select = self::getDb()->select();
		/* @var $select Zend_Db_Select */
		$select->from(static::$_table, static::_getDbAttributes());
		return $select;
	}

	/*public static function getById($id) {
		$id = (int)$id;
		$select = static::_getSelect();
		$select->where($this->_key->getFieldName() . ' = ' . $id);
		$select->limit(1);
		if (($data = self::getDb()->fetchRow($select)) !== false) {
			return new static( $data );
		} else {
			return null;
		}
	}*/

	public static function getList() {
		$select = static::_getSelect();
		return static::_initList($select, func_get_args());
	}

	protected static function _initList(
		Zend_Db_Select $select,
		array $queryComponents
	) {
		$list = RM_Query_Exec::select($select, $queryComponents);
		foreach ($list as &$item) {
			$item = new static($item);
		}
		return $list;
	}

}