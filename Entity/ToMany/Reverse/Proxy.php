<?php
class RM_Entity_ToMany_Reverse_Proxy
    extends
        RM_Entity_ToMany_Abstract_Proxy {

    public function getFromItems() {
        return $this->getIntermediateEntityItems();
    }

    protected function __getIntermediateEntity(RM_Entity_ToMany_Intermediate $intermediate) {
        return $intermediate->getFrom();
    }

    protected function __getCollectionClassName() {
        return 'RM_Entity_ToMany_Reverse_Collection';
    }

    protected function __getIntermediateEntityField() {
        /* @var RM_Entity_ToMany_Intermediate $model */
        $model = $this->_getIntermediateClass();
        return $model::FIELD_TO;
    }

    protected function __createIntermediate(RM_Entity $entity) {
        $model = $this->_getIntermediateClass();
        /* @var RM_Entity_ToMany_Intermediate $intermediate */
        return $model::create($entity, $this->_entity);
    }

}