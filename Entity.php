<?php
abstract class RM_Entity {

	const TABLE_NAME = null;

	protected static $_properties = array();

	/**
	 * @var RM_Entity_Worker
	 */
	private $_entityWorker;

	public function __construct($data = null) {
		if (is_null($data))
			$data = new stdClass();
		$this->_entityWorker = new RM_Entity_Worker(get_called_class(), $data);
	}

	public function __get($name) {
		$val = $this->_entityWorker->getValue($name);
		if (is_null($val)) {
			throw new Exception("Try to get unexpected attribute {$name}");
		} else {
			return $val;
		}
	}

	public function __set($name, $value) {
		if (is_null($this->_entityWorker->setValue($name, $value))) {
			throw new Exception("Try to set unexpected attribute {$name}");
		}
	}

	public function save() {
		$this->_entityWorker->save();
	}

	protected static function _getStorage() {
		$storage = RM_Entity_Storage::getInstance( get_called_class() );
		if (!is_array($storage->getAttributeProperties())) {
			$storage->parse( static::$_properties );
		}
		return $storage;
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

	public static function _getDbAttributes() {
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
	public static function _getSelect() {
		if (is_null(static::TABLE_NAME)) {
			throw new Exception('Table name not setted');
		}
		$select = self::getDb()->select();
		/* @var $select Zend_Db_Select */
		$select->from(static::TABLE_NAME, static::_getDbAttributes());
		static::_setSelectRules( $select );
		return $select;
	}

	public static function _setSelectRules(Zend_Db_Select $select) {}

	/**
	 * @static
	 * @param $id
	 * @return static
	 */
	public static function getById($id) {
		$id = (int)$id;
		if (!(($item = static::_getStorage()->getEntity($id)) instanceof RM_Entity)) {
			$select = static::_getSelect();
			$select->where(
				static::TABLE_NAME . '.' .static::_getKeyAttributeProperties()->getFieldName() . ' = ' . $id
			);
			$item = self::_initItem($select);
			static::_getStorage()->setEntity($id, $item);
		}
		return $item;
	}

	public static function _initItem(Zend_Db_Select $select) {
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

	public static function getCount(RM_Query_Where $where) {
		$select = self::getDb()->select();
		$select->from(static::TABLE_NAME, array(
            'count' => 'COUNT(' . static::_getKeyAttributeProperties()->getFieldName() . ')'
		));
		static::_setSelectRules( $select );
		$where->improveQuery($select);
		return (int)self::getDb()->fetchRow( $select )->count;
	}

	public static function _initList(
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