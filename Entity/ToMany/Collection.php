<?php
class RM_Entity_ToMany_Collection
    extends
        RM_Entity_ToMany_Abstract_Collection {

    protected function __getIntermediateItemId(RM_Entity_ToMany_Intermediate $item) {
        return $item->getIdTo();
    }

    protected function __setIntermediateItemId(RM_Entity_ToMany_Intermediate $item, $idItem) {
        $item->setIdFrom($idItem);
    }

}