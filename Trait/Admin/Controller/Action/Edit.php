<?php
trait RM_Trait_Admin_Controller_Action_Edit {

    public function editAction() {
        if (!$this->__hasEditPageAccess()) {
            $this->__goBack();
            return false;
        }
        parent::editAction();
        $itemClassName = $this->_itemClassName;
        $this->_entity = $this->__getEditableEntity();
        if (!$this->_entity instanceof $itemClassName) {
            $this->__goBack();
        }
        if ($this->getRequest()->isPost()) {
            /** @var stdClass $data */
            $data = (object)$this->getRequest()->getParams();
            try {
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
        } else {
            if (method_exists($this, '__postEntityFields')) {
                $this->__postEntityFields($this->_entity);
            } else {
                $this->__postContentFields();
            }
        }
        $this->__setupEditPage();
        $this->__setupEditPageCrumbs($this->__getCrumbs(), $this->_entity);
    }

    protected function __hasEditPageAccess() {
        return true;
    }

    protected function __setupEditPage() {

    }

    protected function __setupEditPageCrumbs() {

    }

    protected function __getEditableEntity() {
        return $this->_entity;
    }

}