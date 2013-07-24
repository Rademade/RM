<?php
abstract class RM_Entity_ToMany_Abstract_Proxy
    implements
        JsonSerializable {

    /**
     * @var self[]
     */
    protected static $_instances = array();
    protected static $_entitiesKeys = array();
    /**
     * @var RM_Entity
     */
    protected $_entity;
    /**
     * @var string
     */
    private $_intermediateClass;
    /**
     * @var RM_Entity_ToMany_Abstract_Collection
     */
    private $_collection;

    abstract protected function __getIntermediateEntity(RM_Entity_ToMany_Intermediate $intermediate);
    abstract protected function __getCollectionClassName();
    abstract protected function __getIntermediateEntityField();
    abstract protected function __createIntermediate(RM_Entity $entity);

    public function __construct(
        RM_Entity $entity,
        $intermediateClass
    ) {
        $this->_entity = $entity;
        $this->_intermediateClass = $intermediateClass;
    }

    public function destroy() {
        if ($this->_entity instanceof RM_Entity) {
            $key = static::_getAutoEntityId($this->_entity);
            unset(static::$_entitiesKeys[$key]);
            unset(static::$_instances[$key]);
            $this->_entity = null;
        }
        if ($this->_collection instanceof RM_Entity_ToMany_Abstract_Collection) {
            $this->_getCollection()->destroy();
            $this->_collection = null;
        }
    }

    public static function &get(
        RM_Entity $entity,
        $intermediateClass,
        $autoKey = true
    ) {
        $key = join('_', array(
            $intermediateClass,
            $autoKey ?
                self::_getAutoEntityId($entity) :
                (get_class($entity) . '-' . $entity->getId())
        ));
        if (!isset(static::$_instances[$key])) {
            static::$_instances[$key] = new static($entity, $intermediateClass);
        }
        return static::$_instances[$key];
    }

    private static function _getAutoEntityId(RM_Entity $entity) {
        foreach (static::$_entitiesKeys as $key => $storedEntity) {
            if ($storedEntity === $entity) {
                return $key;
            }
        }
        $key = self::_generateUniqueEntityKey();
        static::$_entitiesKeys[$key] = $entity;
        return $key;
    }

    private static function _generateUniqueEntityKey() {
        $key = md5(uniqid() . microtime(true));
        if (isset(static::$_entitiesKeys[$key])) {
            $key = self::_generateUniqueEntityKey();
        }
        return $key;
    }

    /**
     * @param RM_Entity $entity
     *
     * @return RM_Entity_ToMany_Intermediate
     */
    public function add(RM_Entity $entity) {
        $intermediate = $this->__createIntermediate($entity);
        $this->_getCollection()->add($intermediate);
        return $intermediate;
    }

    /**
     * @return RM_Entity_ToMany_Intermediate[]
     */
    public function getItems() {
        return $this->_getCollection()->getIntermediateEntities();
    }

    /**
     * @return RM_Entity
     */
    public function getFirst() {
        $items = $this->getItems();
        $first = reset($items);
        if ($first) {
            return $this->__getIntermediateEntity($first);
        }
        return null;
    }

    /**
     * @return RM_Entity
     */
    public function getLast() {
        $items = $this->getItems();
        $last = end($items);
        if ($last) {
            return $this->__getIntermediateEntity($last);
        }
        return null;
    }

    public function resetItems() {
        $this->_getCollection()->rebuild();
    }

    public function save() {
        $this->_getCollection()->save();
    }

    /**
     * @return RM_Entity[]
     */
    public function getIntermediateEntityItems() {
        $list = array();
        foreach ($this->getItems() as $intermediate) {
            $list[] = $this->__getIntermediateEntity($intermediate);
        }
        return $list;
    }

    public function jsonSerialize() {
        return $this->getItems();
    }

    /**
     * @return RM_Entity_ToMany_Intermediate
     */
    protected function _getIntermediateClass() {
        return $this->_intermediateClass;
    }

    /**
     * @return RM_Entity_ToMany_Abstract_Collection
     */
    private function _getCollection() {
        if (!$this->_collection instanceof RM_Entity_ToMany_Abstract_Collection) {
            $collectionClassName = $this->__getCollectionClassName();
            $this->_collection = new $collectionClassName($this->_entity);
            $this->_collection->resetIntermediateEntities($this->_getIntermediateItems());
        }
        return $this->_collection;
    }

    /**
     * @return RM_Entity_ToMany_Intermediate[]
     */
    private function _getIntermediateItems() {
        $where = new RM_Query_Where();
        /* @var RM_Entity_ToMany_Intermediate $model */
        $model = $this->_getIntermediateClass();
        $where->add($this->__getIntermediateEntityField(), '=', $this->_entity->getId());
        return $model::getList($where);
    }

}