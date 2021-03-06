<?php
/**
 * TODO 1) move caching to other class
 * TODO 2) move attribute storage to other class
 * TODO 3) make wise caching and cache cleaning
 * TODO 4) make attribute validation
 */
abstract class RM_Entity
    implements
        RM_Entity_Search_Result_Interface,
        RM_Interface_Identifiable,
        RM_Interface_Savable {

    const TABLE_NAME = null;
    const CACHE_NAME = null;

    const AUTO_CACHE = true;

    protected static $_properties = array();
    /**
     * @var RM_Entity_Worker_Data
     */
    protected $_entityDataWorker;
    /**
     * @var RM_Entity_Worker_Cache
     */
    protected $_cacheWorker;
    private $_calledClass;

    public function __construct($data = null) {
        $this->_calledClass = get_called_class();
        $this->_entityDataWorker = new RM_Entity_Worker_Data(
            $this->_calledClass,
            is_null($data) ? new stdClass() : $data
        );
    }

    public function destroy() {
        static::_getStorage()->clearData($this->getId());
    }

    /* Manipulation data block */

    public function getId() {
        return $this->__get(
            static::_getKeyAttributeProperties()->getName()
        );
    }

    public function __get($name) {
        $val = $this->_entityDataWorker->getValue($name);
        if (is_null($val)) {
            throw new Exception("Try to get unexpected attribute {$name}");
        } else {
            return $val;
        }
    }

    public function __set($name, $value) {
        if (is_null($this->_entityDataWorker->setValue($name, $value))) {
            throw new Exception("Try to set unexpected attribute {$name}");
        }
    }

    public function save() {
        if ($this->_entityDataWorker->save()) {
            if (static::AUTO_CACHE) {
                $this->__refreshCache();
            }
        }
    }

    /* Cache data block list */

    protected static function _clearCacheList($key) {
        static::getCacher()->remove($key);
    }

    protected static function _loadList($key) {
        return static::getCacher()->load($key);
    }

    protected function _cacheList(array $data, $key) {
        static::getCacher()->cache($data, $key, array());
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
        $this->__cacheEntity($this->getId());
    }

    protected function __cleanCache() {
        $this->_getCacheWorker()->remove((string)$this->getId());
    }

    protected static function __load($key) {
        return static::getCacher()->load($key);
    }

    /* Attribute process block */

    /**
     * @static
     * @return RM_Entity_Attribute_Properties[]
     */
    public static function getAttributesProperties() {
        return static::_getStorage()->getProperties();
    }

    public static function getKeyAttributeName() {
        return self::_getKeyAttributeProperties()->getName();
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
                unset($fields[$key]);
            }
        }
        return $fields;
    }

    /* Load entities block */

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public static function getDb() {
        return RM_Entity_Db::getInstance()->getConnection(get_called_class());
    }

    public static function _setSelectRules(Zend_Db_Select $select) { }

    /**
     * @static
     * @param array $options
     * @throws Exception
     * @return Zend_Db_Select
     */
    public static function _getSelect(array $options = array()) {
        if (is_null(static::TABLE_NAME)) {
            throw new Exception('Table name not setted');
        }
        $select = static::getDb()->select();
        /* @var $select Zend_Db_Select */
        $select->from(static::TABLE_NAME, static::_getDbAttributes());
        static::_setSelectRules($select);
        if (isset($options['no_rule']) && $options['no_rule']) {
            // clear where (removes deleted status condition)
            $select->reset(Zend_Db_Select::WHERE);
        }
        return $select;
    }

    /**
     * @static
     * @param       $id
     * @param array $options
     * @return static
     */
    public static function getById($id, array $options = array()) {
        $id = (int)$id;
        if (is_null($item = static::_getStorage()->getData($id))) {
            if (is_null($item = static::__load($id))) {
                $select = static::_getSelect($options);
                $select->where(
                    static::TABLE_NAME . '.' . static::_getKeyAttributeProperties()->getFieldName() . ' = ' . $id
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
     * @return static
     * @throws Exception
     */
    public static function getFirst() {
        $cacheName = 'FIRST';
        if (is_null($item = static::_getStorage()->getData($cacheName))) {
            $select = static::_getSelect();
            $select->order(static::TABLE_NAME . '.' . static::_getKeyAttributeProperties()->getFieldName() . ' ASC');
            $item = static::_initItem($select);
            static::_getStorage()->setData($item, $cacheName);
        }
        return $item;
    }

    /**
     * @return static
     * @throws Exception
     */
    public static function getLast() {
        $select = static::_getSelect();
        $select->order(static::TABLE_NAME . '.' . static::_getKeyAttributeProperties()->getFieldName() . ' DESC');
        $item = static::_initItem($select);
        return $item;
    }

    /**
     * TODO cache
     * @param array $conditions
     * @param int   $limit
     * @param array $options
     * @return static[]
     * @throws Exception
     */
    public static function find(array $conditions = array(), $limit = 0, array $options = array()) {
        $select = static::_getSelect($options);
        foreach ($conditions as $field => $value) {
            $operand = is_array($value) ? ' IN (?)' : ' = ?';
            $select->where($field . $operand, $value);
        }
        if ($limit !== 0) {
            $select->limit($limit);
        }
        return static::_initList($select, array());
    }

    /**
     * TODO cache
     * @param array $conditions
     * @param array $options
     * @return static
     */
    public static function findOne(array $conditions, array $options = array()) {
        $select = static::_getSelect($options);
        foreach ($conditions as $field => $value) {
            $operand = is_array($value) ? ' IN (?)' : ' = ?';
            $select->where($field . $operand, $value);
        }
        return static::_initItem($select);
    }

    /**
     * @return static[]
     * @throws Exception
     */
    public static function getList() {
        return static::_initList(
            static::_getSelect(),
            func_get_args()
        );
    }

    /**
     * @param Zend_Db_Select $select
     * @return static
     */
    public static function _initItem(Zend_Db_Select $select) {
        $select->limit(1);
        if (($data = static::getDb()->fetchRow($select)) !== false) {
            return static::_initItemFromData($data);
        } else {
            return null;
        }
    }

    public static function column($column) {
        return static::TABLE_NAME . '.' . $column;
    }

    /**
     * @param $data
     * @return static
     */
    protected static function _initItemFromData($data) {
        return new static($data);
    }

    /**
     * @param RM_Query_Where $where
     * @param RM_Query_Join  $join
     * @return int
     */
    public static function getCount(
        RM_Query_Where $where = null,
        RM_Query_Join $join = null
    ) {
        $select = static::_getSelect();
        if ($where instanceof RM_Query_Where) {
            $where->improveQuery($select);
        }
        if ($join instanceof RM_Query_Join) {
            $join->improveQuery($select);
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

    /**
     * @param Zend_Db_Select $select
     * @param array          $queryComponents
     * @return static[]
     */
    public static function _initList(
        Zend_Db_Select $select,
        array $queryComponents
    ) {
        $list = RM_Query_Exec::select($select, $queryComponents);
        foreach ($list as &$item) {
            $item = static::_initItemFromData($item);
        }
        return $list;
    }

    /* Event manager */

    public static function getEntityEventManager() {
        return self::_getStorage()->getEventManager();
    }

    /* Entity storage data block */

    public static function &_getStorage() {
        $storage = RM_Entity_Storage::getInstance(get_called_class());
        if (!is_array($storage->getProperties())) {
            $storage->parse(static::$_properties);
        }
        return $storage;
    }

    public static function getCacher() {
        return static::_getStorage()->getCacher();
    }

}