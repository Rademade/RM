<?php
trait Fin_Trait_Controller_AddAction {

    public function addAction() {
        if (!$this->__hasAddPageAccess()) {
            $this->__goBack();
        }
        parent::addAction();
        $this->__setupAddPageCrumbs();
        if ($this->getRequest()->isPost()) {
            /** @var stdClass $data */
            $data = (object)$this->getRequest()->getParams();
            try {
                $itemClassName = $this->_itemClassName;
                $this->_entity = $this->__createEntity($data, $itemClassName);
                if (method_exists($this, '__setData')) {
                    $this->__setData($this->_entity, $data);
                } else {
                    $this->__postContentFields();
                }
                if (method_exists($this->_entity, 'validate')) {
                    $this->_entity->validate();
                }
                if (method_exists($this, '__saveEntity')) {
                    $this->__saveEntity($this->_entity, $data);
                } else {
                    $this->_entity->save();
                }
                $this->__goBack();
            } catch (Exception $e) {
                $this->view->showMessage($e);
            }
        }
    }

    protected function __createEntity(stdClass $data, $className) {
        return $className::create();
    }

    protected function __hasAddPageAccess() {
        return true;
    }

    protected function __setupAddPageCrumbs() {

    }

}