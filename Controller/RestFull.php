<?php
abstract class RM_Controller_RestFull
    extends
        RM_Controller_Ajax {

    /**
     * @var string
     */
    protected $_method;

    /**
     * @var RM_Compositor
     */
    protected $_data;

    public function preDispatch() {
        parent::preDispatch();
        $this->_setResponseJSON();
        $this->_method = $this->getRequest()->getMethod();
        $this->_data = new RM_Compositor(
            Zend_Json::decode( $this->getRequest()->getRawBody() ),
            parent::getAllParams()
        );
    }

    public function indexAction() {
        try {
            $this->_result = $this->hasParam('id') ?
                $this->__itemMethods() :
                $this->__listMethods();
        } catch (Exception $e) {
            $this->_result->status = 0;
            $this->_result->errorMessage = $e->getMessage();
        }
    }

    public function showList() {
        return [];
    }

    public function showItem() {
        return false;
    }
    
    public function createItem() {
        return false;
    }

    public function updateItem() {
        return false;
    }

    public function removeItem() {
        return false;
    }

    public function getParam($name, $default = null) {
        return isset($this->_data->$name) ? $this->_data->$name : $default;
    }

    public function getAllParams() {
        return $this->_data;
    }

    protected function __itemMethods() {
        switch ($this->_method) {
            case 'GET':
                return $this->showItem();
            case 'PATCH':
            case 'PUT':
                return $this->updateItem();
            case 'DELETE':
                return $this->removeItem();
        }
    }

    protected function __listMethods() {
        switch ($this->_method) {
            case 'GET':
                return $this->showList();
            case 'POST':
                return $this->createItem();
        }
    }

}