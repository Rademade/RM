<?php
class RM_Head
    extends
        Zend_Controller_Plugin_Abstract {

    /**
     * @var RM_Head
     */
    private static $_self;

    /**
     * @var array
     */
    protected static $_links = array();

    /**
     * @var RM_Head_JS
     */
    private $_js;
    /**
     * @var RM_Head_CSS
     */
    private $_css;

    /**
     * @return RM_Head
     */
    public static function getInstance() {
        return self::$_self;
    }

    public static function getModuleName() {
        if (isset(static::$_links[Layouts::$moduleName])) {
            return static::$_links[Layouts::$moduleName];
        }
        return Layouts::$moduleName;
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request = null) {
        $this->_init();
        $this->_load();
        self::$_self = $this;
    }

    public function _init() {
        $cfg = new Zend_Config_Ini( APPLICATION_PATH . '/configs/views/' . static::getModuleName() . '.ini' );
        $this->_initJsCompressor($cfg);
        $this->_initCssCompressor($cfg);
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

    /**
     * @param $cfg
     */
    private function _initJsCompressor($cfg) {
        $dependencies = RM_Dependencies::getInstance();
        if (isset($dependencies->jsCompressorClass)) {
            $jsCompressorClass = $dependencies->jsCompressorClass;
        } else {
            $jsCompressorClass = 'RM_Head_JS';
        }
        $this->_js = new $jsCompressorClass($cfg->js);
    }

    /**
     * @param $cfg
     */
    private function _initCssCompressor($cfg) {
        $dependencies = RM_Dependencies::getInstance();
        if (isset($dependencies->cssCompressorClass)) {
            $cssCompressorClass = $dependencies->cssCompressorClass;
        } else {
            $cssCompressorClass = 'RM_Head_CSS';
        }
        $this->_css = new $cssCompressorClass($cfg->css);
    }

}