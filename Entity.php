<?php
abstract class RM_Entity {

	const TABLE_NAME = '';

	protected static $_properties = array();

	/**
	 * @var RM_Entity_Worker
	 */
	private $_dbWorker;

	public function __construct($data = null) {
		if (is_null($data)) {
			$data = new stdClass();
		}
		$this->_dbWorker = new RM_Entity_Worker(
			get_called_class(),
			$data,
			static::TABLE_NAME
		);
	}

	public function __get($name) {
		$val = $this->_dbWorker->getValue($name);
		if (is_null($val)) {
			throw new Exception("Try to get unexpected attribute {$name}");
		} else {
			return $val;
		}
	}

	public function __set($name, $value) {
		if (is_null($this->_dbWorker->setValue($name, $value))) {
			throw new Exception("Try to set unexpected attribute {$name}");
		}
	}

	public function save() {
		$this->_dbWorker->save();
	}

	protected static function _getStorage() {
		$storge = RM_Entity_Storage::getInstance( get_called_class() );
		if (!is_array($storge->getAttributeProperties())) {
			$storge->parse( static::$_properties );
		}
		return $storge;
	}

	/**
	 * @static
	 * @return RM_Entity_Attribute_Properties[]
	 */
	public static function getAttributesProperties() {
		return static::_getStorage()->getAttributeProperties();
	}

	protected static function &_getKeyAttributeProperties() {
		return static::_getStorage()->getKeyAttributeProperties();
	}

	protected static function _getDbAttributes() {
		return static::_getStorage()->getFieldNames();
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
		$select->from(static::TABLE_NAME, static::_getDbAttributes());
		return $select;
	}

	public static function getById($id) {
		$id = (int)$id;
		$select = static::_getSelect();
		$select->where(
			static::TABLE_NAME . '.' .static::_getKeyAttributeProperties()->getFieldName() .
			' = ' . $id
		);
		return self::_initItem($select);
	}

	protected static function _initItem(Zend_Db_Select $select) {
		$select->limit(1);
		if (($data = self::getDb()->fetchRow($select)) !== false) {
			return new static( $data );
		} else {
			return null;
		}
	}

	public static function getList() {
		return static::_initList(
			static::_getSelect(),
			func_get_args()
		);
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