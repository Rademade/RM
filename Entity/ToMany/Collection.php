<?php
class RM_Entity_ToMany_Collection {

    /**
     * @var int
     */
    private $_idFrom;

    /**
     * @var RM_Entity_ToMany_Intermediate[]
     */
    private $_itemAdd = array();
    /**
     * @var RM_Entity_ToMany_Intermediate[]
     */
    private $_itemRemove = array();
    /**
     * @var RM_Entity_ToMany_Intermediate[]
     */
    private $_items = array();

    /**
     * RM_Entity_ToMany_Intermediate
     * @var string
     */
    private $_intermediateClass;

    public function __construct(RM_Entity $from, $intermediateClass) {
        $this->_intermediateClass = $intermediateClass;//TODO check if instanceof RM_Entity_ToMany_Intermediate
        $this->_idFrom = (int)$from->getId();
        $this->_load();
    }

    public function getIdFrom() {
        return $this->_idFrom;
    }

    public function rebuild() {
        foreach ($this->getIntermediateEntities() as $item) {
            $this->remove( $item );
        }
    }

    public function add(RM_Entity_ToMany_Intermediate $item) {
        if (!array_key_exists(
            $item->getId(),
            $this->_items
        )) {
            if (array_key_exists(
                $item->getId(),
                $this->_itemRemove
            )) {
                unset( $this->_itemRemove[ $item->getId() ] );
            }
            $this->_items[ $item->getId() ] = $item;
            $this->_itemAdd[ $item->getId() ] = $item;
        }
    }

    public function remove(RM_Entity $item) {
        if ( array_key_exists(
            $item->getId(),
            $this->_items
        ) ) {
            $this->_itemRemove[ $item->getId() ] = $item;
            unset( $this->_items[ $item->getId() ] );
            unset( $this->_itemAdd[ $item->getId() ] );
        }
    }

    public function save() {
        foreach ($this->_itemAdd as $id => $item) {
            $item->save();
            unset($this->_itemAdd[$id]);
        }
        foreach ($this->_itemRemove as $id => $item) {
            $item->remove();
            unset($this->_itemRemove[$id]);
        }

    }

    /**
     * @return RM_Entity_ToMany_Intermediate[]
     */
    public function getIntermediateEntities() {
        return $this->_items;
    }

    private function _load() {
        $conditions = new RM_Query_Where();
        /* @var RM_Entity_ToMany_Intermediate $model */
        $model = $this->_intermediateClass;
        $conditions->add(
            $model::FIELD_FROM,
            RM_Query_Where::EXACTLY,
            $this->getIdFrom()
        );
        foreach ($model::getList($conditions) as $item) {
            /* @var RM_Entity_ToMany_Intermediate $item */
            $this->_items[ $item->getId() ] = $item;
        }
    }

}