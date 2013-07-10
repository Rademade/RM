<?php
class RM_Entity_ToMany_Reverse_Collection
    extends
        RM_Entity_ToMany_Abstract_Collection {

    protected function __getIntermediateItemId(RM_Entity_ToMany_Intermediate $item) {
        return $item->getIdFrom();
    }

    protected function __setIntermediateItemId(RM_Entity_ToMany_Intermediate $item, $idItem) {
        $item->setIdTo($idItem);
    }

}