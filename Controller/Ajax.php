<?php
abstract class RM_Controller_Ajax
    extends
        RM_Controller_Base_Abstract {

    /**
     * @var stdClass
     */
    protected $_data;
    /**
     * @var stdClass|string
     */
    protected $_result;

    private $_responseType;

    /**
     * @var RM_User_Session
     */
    protected $_userSession;

    protected $_idUser;
    /**
     * @var RM_User_Interface
     */
    protected $_user;

    const TYPE_JSON = 1;
    const TYPE_HTML = 2;

    protected function _setResponseHTML() {
        $this->_responseType = self::TYPE_HTML;
        $this->_result = '';
    }

    protected function _setResponseJSON() {
        $this->_responseType = self::TYPE_JSON;
        $this->_result = new stdClass();
    }

    public function preDispatch() {
        parent::preDispatch();
        $this->__disableView();
        $this->_userSession = RM_User_Session::getInstance();
        $this->_user = $this->_userSession->getUser();
        $this->_idUser = $this->_user instanceof RM_User_Interface ? $this->_user->getId() : 0;
        $this->_data = (object)array_merge(
            $this->getRequest()->getPost(),
            $_GET
        );
    }

    public function postDispatch() {
        parent::postDispatch();
        switch ($this->_responseType) {
            case self::TYPE_JSON:
                $this->_renderJSON();
                break;
            case self::TYPE_HTML:
                $this->_renderHTML();
                break;
        }
    }

    public function renderViewScript($name, $args) {
        $this->_setResponseHTML();
        $cfg = Zend_Registry::get('cfg');
        $view = new Zend_View();
        $view->setHelperPath( $cfg['resources']['view']['basePath'] . 'helpers/' );
        $view->setScriptPath( $cfg['resources']['view']['basePath'] . 'scripts/' );
        $view->assign($args);
        $this->_result = $view->render($name);
    }

    private function _renderJSON() {
        $response = $this->getResponse();
        $output = Zend_Json::encode( $this->_result );
        $response->setBody($output);
        $response->setHeader('content-type', 'application/json', true);
    }

    private function _renderHTML() {
        $response = $this->getResponse();
        $response->setBody( join('', array(
            $this->_result
        ) ) );
        $response->setHeader('content-type', 'text/plain', true);
    }

}