<?php
/**
 * TODO 1) move caching to other class
 * TODO 2) move attribute storage to other class
 * TODO 3) make wise caching and cache cleaning
 * TODO 4) make attribute validation
 */
abstract class RM_Entity
    implements
        RM_Entity_Search_Result_Interface {

	const TABLE_NAME = null;

	const CACHE_NAME = null;
	const AUTO_CACHE = true;

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
		if (is_null($data)) {
			$data = new stdClass();
        }
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
//        if ($this->_dataWorker instanceof RM_Entity_Worker_Data) {
            if (is_null($this->_dataWorker->setValue($name, $value))) {
                throw new Exception("Try to set unexpected attribute {$name}");
            }
//        } else {
//            throw new Exception("Try to set '{$name}''");
//        }
	}

	public function save() {
		if ($this->_dataWorker->save()) {
			if (static::AUTO_CACHE) {
				$this->__refreshCache();
			}
		}
	}

	/* Cache data block list */

	protected static function _clearCacheList($key) {
		static::_getStorage()->getCacher()->remove( $key );
	}

	protected static function _loadList($key) {
		return static::_getStorage()->getCacher()->load( $key );
	}

	protected function _cacheList(array $data, $key) {
		static::_getStorage()->getCacher()->cache($data, $key, array());
	}

	/* Cache data block item */
	protected function _getCacheWorker() {
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
		$this->_getCacheWorker()->remove( (string)$this->getId() );
	}

	protected static function __load($key) {
		return static::_getStorage()->getCacher()->load( $key );
	}

	/* Attribute process block */

	/**
	 * @static
	 * @return RM_Entity_Attribute_Properties[]
	 */
	public static function getAttributesProperties() {
		return static::_getStorage()->getProperties();
	}

    public static function getKeyAttributeField() {
        return self::_getKeyAttributeProperties()->getFieldName();
    }

	protected static function &_getKeyAttributeProperties() {
		return static::_getStorage()->getKeyProperties();
	}

	public static function _getDbAttributes() {
		return static::_getStorage()->getFieldNames();
	}

    public static function _getSecondaryDbAttributes() {
        $fields = static::_getDbAttributes();
        foreach ($fields as $key => $field) {
            if ($field === static::_getKeyAttributeProperties()->getName()) {
                unset($fields[ $key ]);
            }
        }
        return $fields;
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
     * @throws Exception
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
				if ($item instanceof self) {
					$item->__cache();
				}
			}
			static::_getStorage()->setData($item, $id);
		}
		return $item;
	}

    /**
     * TODO cache
     * @param array $conditions
     * @param int $limit
     * @return RM_Entity[]
     */
    public static function find(array $conditions = array(), $limit = 0) {
        $select = static::_getSelect();
        foreach ($conditions as $field => $value) {
            $select->where($field . ' = ?', $value);
        }
        if ($limit === 0) {
            $select->limit( $limit );
        }
        return static::_initList( $select, array() );
    }

    /**
     * TODO cache
     * @param array $conditions
     * @return RM_Entity
     */
    public static function findOne(array $conditions) {
        $select = static::_getSelect();
        foreach ($conditions as $field => $value) {
            $select->where($field . ' = ?', $value);
        }
        return static::_initItem( $select );
    }

    public static function getList() {
        return static::_initList(
            static::_getSelect(),
            func_get_args()
        );
    }

	public static function _initItem(Zend_Db_Select $select) {
		$select->limit(1);
		if (($data = self::getDb()->fetchRow($select)) !== false) {
			return new static( $data );
		} else {
			return null;
		}
	}

	public static function getCount(RM_Query_Where $where = null) {
		$select = static::_getSelect();
        if ($where instanceof RM_Query_Where) {
            $where->improveQuery($select);
        }
        $select->limit(1);
        return RM_Query_Exec::getRowCount(
            $select,
            join('.', array(
                static::TABLE_NAME,
                static::getKeyAttributeField()
            ))
        );
	}

	public static function _initList(
		Zend_Db_Select $select,
		array $queryComponents
	) {
		$list = RM_Query_Exec::select($select, $queryComponents);
        foreach ($list as &$item) {
			$item = new static( $item );
		}
		return $list;
	}

	/* Entity storage data block */

	public static function &_getStorage() {
		$storage = RM_Entity_Storage::getInstance( get_called_class() );
		if (!is_array($storage->getProperties())) {
			$storage->parse( static::$_properties );
		}
		return $storage;
	}

}
