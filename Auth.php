<?php
class RM_Auth {

    /**
     * @var Zend_Controller_Action
     */
    private $_controller;

    /**
     * @var Zend_Auth_Adapter_Http_Resolver_Interface
     */
    private $_authResolver;

    private $_authAdapterClassName;

    /**
     * @param Zend_Controller_Action $controller
     * @param Zend_Auth_Adapter_Http_Resolver_Interface $resolver
     * @param $authAdapterName
     */
    public function __construct(
        Zend_Controller_Action $controller,
        Zend_Auth_Adapter_Http_Resolver_Interface $resolver,
        $authAdapterName
    ) {
        $this->_controller = $controller;
        $this->_authResolver = $resolver;
        $this->_authAdapterClassName = $this->_validateAdapter( $authAdapterName );
    }

    /**
     * @return Zend_Auth_Result
     */
    public function authenticate() {
        $auth = Zend_Auth::getInstance();
        return $auth->authenticate( $this->_getHttpAuth() );
    }

    private function _getHttpAuth() {
        /* @var Zend_Auth_Adapter_Http $httpAuth*/
        $httpAuth = new $this->_authAdapterClassName(array(
            'accept_schemes' => 'basic',
            'realm' => Zend_Registry::get('cfg')['domain']
            //TODO add params
        ) );
        //Zend_Auth_Adapter_Interface
        $httpAuth->setRequest( $this->_controller->getRequest() );
        $httpAuth->setResponse( $this->_controller->getResponse() );
        $httpAuth->setBasicResolver( $this->_authResolver );
        return $httpAuth;
    }

    private function _validateAdapter( $authAdapterName ) {
        $classRef = new Zend_Reflection_Class( $authAdapterName );
        if (!$classRef->isSubclassOf('Zend_Auth_Adapter_Http')) {
            throw RM_Auth_Exception::wrongAdapter();
        }
        return $authAdapterName;
    }

}