<?php
abstract class RM_Entity_ToMany_Abstract_Collection {

    /**
     * @var RM_Entity
     */
    private $_entity;
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

    abstract protected function __getIntermediateItemId(RM_Entity_ToMany_Intermediate $item);
    abstract protected function __setIntermediateItemId(RM_Entity_ToMany_Intermediate $item, $idItem);

    public function __construct(RM_Entity $entity) {
        $this->_entity = $entity;
    }

    public function getIdEntity() {
        return $this->_entity->getId();
    }

    public function rebuild() {
        foreach ($this->getIntermediateEntities() as $item) {
            $this->remove($item);
        }
    }

    public function add(RM_Entity_ToMany_Intermediate $item) {
        $intermediateItemId = $this->__getIntermediateItemId($item);
        if (!array_key_exists(
            $intermediateItemId,
            $this->_items
        )) {
            if (array_key_exists(
                $intermediateItemId,
                $this->_itemRemove
            )
            ) {
                unset($this->_itemRemove[$intermediateItemId]);
            }
            $this->_items[$intermediateItemId] = $item;
            $this->_itemAdd[$intermediateItemId] = $item;
        }
    }

    public function remove(RM_Entity_ToMany_Intermediate $item) {
        $intermediateItemId = $this->__getIntermediateItemId($item);
        if (array_key_exists(
            $intermediateItemId,
            $this->_items
        )) {
            $this->_itemRemove[$intermediateItemId] = $item;
            unset($this->_items[$intermediateItemId]);
            unset($this->_itemAdd[$intermediateItemId]);
        }
    }

    public function save() {
        foreach ($this->_itemAdd as $id => $item) {
            $this->__setIntermediateItemId($item, $this->getIdEntity());
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

    /**
     * @param RM_Entity_ToMany_Intermediate[] $items
     *
     * @throws Exception
     */
    public function resetIntermediateEntities(array $items) {
        $this->_items = array();
        $this->_itemAdd = array();
        $this->_itemRemove = array();
        foreach ($items as $item) {
            if ($item instanceof RM_Entity_ToMany_Intermediate) {
                $this->_items[$this->__getIntermediateItemId($item)] = $item;
            } else {
                throw new Exception('Not intermediate class given');
            }
        }
    }

}