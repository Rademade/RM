<?php
class RM_Entity_ToMany_Collection {

    /**
     * @var RM_Entity
     */
    private $_from;

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

    public function __construct(RM_Entity $from) {
        $this->_from = $from;
    }

    public function getIdFrom() {
        return $this->_from->getId();
    }

    public function rebuild() {
        foreach ($this->getIntermediateEntities() as $item) {
            $this->remove( $item );
        }
    }

    public function add(RM_Entity_ToMany_Intermediate $item) {
        if (!array_key_exists(
            $item->getIdTo(),
            $this->_items
        )) {
            if ( array_key_exists(
                $item->getIdTo(),
                $this->_itemRemove
            ) ) {
                unset( $this->_itemRemove[ $item->getIdTo() ] );
            }
            $this->_items[ $item->getIdTo() ] = $item;
            $this->_itemAdd[ $item->getIdTo() ] = $item;
        }
    }

    public function remove(RM_Entity_ToMany_Intermediate $item) {
        if ( array_key_exists(
            $item->getIdTo(),
            $this->_items
        ) ) {
            $this->_itemRemove[ $item->getIdTo() ] = $item;
            unset( $this->_items[ $item->getIdTo() ] );
            unset( $this->_itemAdd[ $item->getIdTo() ] );
        }
    }

    public function save() {
        foreach ($this->_itemAdd as $id => $item) {
            $item->setIdFrom( $this->getIdFrom() );
            $item->save();
            unset( $this->_itemAdd[ $id ] );
        }
        foreach ($this->_itemRemove as $id => $item) {
            $item->remove();
            unset( $this->_itemRemove[ $id ] );
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
     * @throws Exception
     */
    public function resetIntermediateEntities(array $items) {
        $this->_items = array();
        $this->_itemAdd = array();
        $this->_itemRemove = array();
        foreach ($items as $item) {
            if ($item instanceof RM_Entity_ToMany_Intermediate) {
                $this->_items[ $item->getIdTo() ] = $item;
            } else {
                throw new Exception('Not intermediate class given');
            }
        }
    }


}