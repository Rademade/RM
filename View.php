<?php
class RM_View
    extends
        Zend_View {

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->setScriptPath( dirname(__FILE__) . '/View/scripts' );
        $this->setHelperPath( dirname(__FILE__) . '/View/Helper' );
    }

}