<?php
#todo comments - list of methods
#
trait RM_Trait_Admin_Controller_Action_Edit {

    public function editAction() {
        if (!$this->__hasEditPageAccess()) {
            $value = $this->__noEditPageAccess();
            if (null !== $value) return $value;
        }
        parent::editAction();
        $itemClassName = $this->_itemClassName;
        $this->_entity = $this->__getEditableEntity();
        if (!$this->_entity instanceof $itemClassName) {
            $value = $this->__noEditableEntity();
            if (null !== $value) return $value;
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
                if (method_exists($this, '__afterSave')) {
                    $value = $this->__afterSave($this->_entity, $data);
                    if (null !== $value) return $value;
                } else {
                    $this->__goBack();
                }
            } catch (Exception $e) {
                $this->__showMessage($e);
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

    protected function __noEditPageAccess() {
        $this->__goBack();
        return false;
    }

    protected function __noEditableEntity() {
        $this->__goBack();
        return false;
    }

    protected function __showMessage($message) {
        $this->view->showMessage($message);
    }

}