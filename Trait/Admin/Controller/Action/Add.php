<?php
trait RM_Trait_Admin_Controller_Action_Add {

    public function addAction() {
        if (!$this->__hasAddPageAccess()) {
            $this->__goBack();
            return false;
        }
        parent::addAction();
        if ($this->getRequest()->isPost()) {
            /** @var stdClass $data */
            $data = (object)$this->getRequest()->getParams();
            try {
                $itemClassName = $this->_itemClassName;
                $this->_entity = $this->__createEntity($data, $itemClassName);
                if (method_exists($this, '__updateEntity')) {
                    $this->__updateEntity($this->_entity, $data);
                } else {
                    $this->__setContentFields();
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
        $this->__setupAddPage();
        $this->__setupAddPageCrumbs($this->__getCrumbs());
    }

    protected function __createEntity(stdClass $data, $className) {
        return $className::create();
    }

    protected function __hasAddPageAccess() {
        return true;
    }

    protected function __setupAddPage() {

    }

    protected function __setupAddPageCrumbs() {

    }

}