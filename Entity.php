<?php
abstract class RM_Entity {

	const TABLE_NAME = null;

	const CACHE_NAME = null;

	protected static $_properties = array();

	/**
	 * @var RM_Entity_Worker_Data
	 */
	private $_dataWorker;

	private $_calledClass;

	/**
	 * @var RM_Entity_Worker_Cache
	 */
	protected $_cacheWorker;

	public function __construct($data = null) {
		if (is_null($data))
			$data = new stdClass();
		$this->_calledClass = get_called_class();
		$this->_dataWorker = new RM_Entity_Worker_Data($this->_calledClass, $data);
	}

	/* Manipulation data block */

	public function getId() {
		return $this->__get(
			static::_getKeyAttributeProperties()->getName()
		);
	}

	public function __get($name) {
		$val = $this->_dataWorker->getValue($name);
		if (is_null($val)) {
			throw new Exception("Try to get unexpected attribute {$name}");
		} else {
			return $val;
		}
	}

	public function __set($name, $value) {
		if (is_null($this->_dataWorker->setValue($name, $value))) {
			throw new Exception("Try to set unexpected attribute {$name}");
		}
	}

	public function save() {
		if ($this->_dataWorker->save()) {
			$this->__refreshCache();
		}
	}

	/* Cache data block list */

	protected static function _clearCacheList($key) {
		static::_getStorage()
			->getCacher(get_called_class())
			->remove( $key );
	}

	protected static function _loadList($key) {
		return static::_getStorage()
			->getCacher(get_called_class())
			->load( $key );
	}

	protected function _cacheList(array $data, $key) {
		return static::_getStorage()
			->getCacher(get_called_class())
			->cache($data, $key, array());
	}


	/* Cache data block item */
	public function _getCacheWorker() {
		if (is_null($this->_cacheWorker)) {
			$this->_cacheWorker = new RM_Entity_Worker_Cache($this->_calledClass);
		}
		return $this->_cacheWorker;
	}
	
	public function __refreshCache() {
		$this->__cache();
	}

	protected function __cachePrepare() {

	}

	protected function __getCacheTags() {
		return array((string)$this->getId());
	}

	protected function __cacheEntity($key) {
		$this->_getCacheWorker()->cache($this, $key, $this->__getCacheTags());
	}

	protected function __cache() {
		$this->__cachePrepare();
		$this->__cacheEntity( $this->getId() );
	}

	protected function __cleanCache() {
		$this->_getCacheWorker()->clean( (string)$this->getId() );
	}

	protected static function __load($key) {
		return static::_getStorage()
			->getCacher(get_called_class())
			->load( $key );
	}

	/* Attribute process block */

	/**
	 * @static
	 * @return RM_Entity_Attribute_Properties[]
	 */
	public static function getAttributesProperties() {
		return static::_getStorage()->getProperties();
	}

	protected static function &_getKeyAttributeProperties() {
		return static::_getStorage()->getKeyProperties();
	}

	public static function _getDbAttributes() {
		return static::_getStorage()->getFieldNames();
	}

	/* Load entities block */

	/**
	 * @static
	 * @return Zend_Db_Adapter_Abstract
	 */
	public static function getDb() {
		return Zend_Registry::get('db');
	}

	public static function _setSelectRules(Zend_Db_Select $select) {}

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

	/**
	 * @static
	 * @param $id
	 * @return static
	 */
	public static function getById($id) {
		$id = (int)$id;
		if (is_null($item = static::_getStorage()->getData($id))) {
			if (is_null($item = static::__load($id))) {
				$select = static::_getSelect();
				$select->where(
					static::TABLE_NAME . '.' .static::_getKeyAttributeProperties()->getFieldName() . ' = ' . $id
				);
				$item = static::_initItem($select);
				$item->__cache();
			}
			static::_getStorage()->setData($item, $id);
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

	/* Entity storage data block */

	protected static function _getStorage() {
		$storage = RM_Entity_Storage::getInstance( get_called_class() );
		if (!is_array($storage->getProperties())) {
			$storage->parse( static::$_properties );
		}
		return $storage;
	}

}
