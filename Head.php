<?php
class RM_Head
    extends
        Zend_Controller_Plugin_Abstract {

    /**
     * @var RM_Head
     */
    private static $_self;
    /**
     * @var RM_Head_JS
     */
    private $_js;
    /**
     * @var RM_Head_CSS
     */
    private $_css;

    /**
     * @return Head
     */
    public static function getInstance() {
        return self::$_self;
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $this->_init();
        $this->_load();
        self::$_self = $this;
    }

    public function _init() {
        $cfg = new Zend_Config_Ini( APPLICATION_PATH . '/configs/views/' . Layouts::$moduleName . '.ini' );
        $this->_js = new RM_Head_JS( $cfg->js );
        $this->_css = new RM_Head_CSS( $cfg->css );
    }

    public function _load() {
        try {
            $this->getCSS()->add('base');
        } catch (Exception $e) {

        }
        try {
            $this->getJS()->add('base');
        } catch (Exception $e) {

        }
    }

    public function getJS() {
        return $this->_js;
    }

    public function getCSS() {
        return $this->_css;
    }

    public function getView() {
        return Zend_Layout::getMvcInstance()->getView();
    }


}