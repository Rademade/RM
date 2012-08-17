<?php
class RM_Entity_ToMany_Proxy {

    private static $_instances = array();

    /**
     * @var RM_Entity
     */
    private $_from;

    /**
     * RM_Entity_ToMany_Collection
     * @var string
     */
    private $_intermediateClass;

    /**
     * @var RM_Entity_ToMany_Collection
     */
    private $_collection;

    private function __construct(
        RM_Entity $from,
        $intermediateClass
    ) {
        $this->_from = $from;
        $this->_intermediateClass = $intermediateClass;
    }

    public static function get(
        RM_Entity $from,
        $intermediateClass
    ) {
        //TODO check if is real intermediate class!
        $key = join( '_', array(
            get_class( $from ),
            $intermediateClass,
            $from->getId()
        ) );
        if (!isset(self::$_instances[ $key ])) {
            self::$_instances[ $key ] = new self($from, $intermediateClass);
        }
        return self::$_instances[ $key ];
    }

    /**
     * @return RM_Entity[]
     */
    public function getToItems() {
        $list = array();
        foreach ($this->_getCollection()->getIntermediateEntities() as $intermediate) {
            $list[] = $intermediate->getTo();
        }
        return $list;
    }

    /**
     * @param RM_Entity $to
     * @return RM_Entity_ToMany_Intermediate
     */
    public function add(RM_Entity $to) {
        $model = $this->_getIntermediateClass();
        /* @var RM_Entity_ToMany_Intermediate $intermediate */
        $intermediate = $model::create( $this->_from, $to );
        $this->_getCollection()->add( $intermediate );
        return $intermediate;
    }

    public function resetItems() {
        $this->_getCollection()->rebuild();
    }

    public function save() {
        $this->_getCollection()->save();
    }

    /**
     * @return RM_Entity_ToMany_Collection
     */
    private function _getCollection() {
        if (!$this->_collection instanceof RM_Entity_ToMany_Collection) {
            $this->_collection = new RM_Entity_ToMany_Collection( $this->_from );
            $this->_collection->resetIntermediateEntities( $this->_getIntermediateItems() );
        }
        return $this->_collection;
    }

    /**
     * @return RM_Entity_ToMany_Intermediate
     */
    private function _getIntermediateClass() {
        return $this->_intermediateClass;
    }

    /**
     * @return RM_Entity_ToMany_Intermediate[]
     */
    private function _getIntermediateItems() {
        $where = new RM_Query_Where();
        /* @var RM_Entity_ToMany_Intermediate $model */
        $model = $this->_intermediateClass;
        $where->add($model::FIELD_FROM, '=', $this->_from->getId());
        return $model::getList($where);
    }

}