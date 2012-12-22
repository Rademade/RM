<?php
abstract class RM_Head_Abstract {

    /**
     * @var Zend_View
     */
    private $_view;

    /**
     * @return Zend_View
     */
    public function getView() {
        if (!$this->_view instanceof Zend_View) {
            $this->_view  = Zend_Layout::getMvcInstance()->getView();
        }
        return $this->_view;
    }

    protected function __getPath($path, $filePath) {
        if ($this->_isRemote($filePath)) {
            return $filePath;
        } else {
            return $path . $filePath;
        }
    }

    protected function __getFullPath($path, $filePath) {
        if ($this->_isRemote($filePath)) {
            return $filePath;
        } else {
            return PUBLIC_PATH . $path . $filePath;
        }
    }

    private function _isRemote($filePath) {
        return preg_match('/^https?\:\/\//', $filePath);
    }

}