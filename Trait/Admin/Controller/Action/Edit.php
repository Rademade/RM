<?php
trait Fin_Trait_Controller_EditAction {

    public function editAction() {
        if (!$this->__hasEditPageAccess()) {
            $this->__goBack();
        }
        parent::editAction();
        $this->_entity = $this->__getEditableEntity();
        if (!$this->_entity) {
            $this->__goBack();
        }
        $this->__setupEditPageCrumbs($this->_entity);
        if ($this->getRequest()->isPost()) {
            /** @var stdClass $data */
            $data = (object)$this->getRequest()->getParams();
            try {
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
        } else {
            if (method_exists($this, '__postFields')) {
                $this->__postFields($this->_entity);
            } else {
                $this->__postContentFields();
            }
        }
    }

    protected function __hasEditPageAccess() {
        return true;
    }

    protected function __setupEditPageCrumbs(RM_Entity $e) {

    }

    protected function __getEditableEntity() {
        return $this->_entity;
    }

}