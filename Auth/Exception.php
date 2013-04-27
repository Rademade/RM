<?php
class RM_Auth_Exception extends Exception {

    public static function wrongAdapter() {
        return new self('Wrong auth adapter class name given. Class must extend Zend_Auth_Adapter_Http');
    }

}