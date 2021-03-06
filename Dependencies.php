<?php
class RM_Dependencies
    extends
        stdClass {

    private $_storage = array();

    private $_default = array(
        'userClass' => 'RM_User',
        'userProfile' => 'RM_User',
        'pageClass' => 'RM_Page',
        'phoneClass' => 'RM_Phone',
        'photoResizerClass' => 'RM_Photo_Resizer'
    );

    /**
     * @var RM_Dependencies
     */
    private static $_self;

    private function __construct() { }

    /**
     * @static
     * @return RM_Dependencies
     */
    public static function getInstance() {
        if (!self::$_self instanceof self) {
            self::$_self = new self();
        }
        return self::$_self;
    }

    public function __set($key, $val) {
        $this->_storage[$key] = $val;
    }

    public function __get($key) {
        if (!isset($this->_storage[$key]) && isset($this->_default[$key])) {
            $this->_storage[$key] = $this->_default[$key];
        }
        return $this->_storage[$key];
    }

    public function __isset($key) {
        return isset($this->_storage[$key]) || isset($this->_default[$key]);
    }

}