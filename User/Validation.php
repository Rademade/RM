<?php
abstract class RM_User_Validation {

    /**
     * @var RM_Content_Field_Process_Line
     */
    private $_lineProcessor;

    protected function _getLineProcessor() {
        if (!($this->_lineProcessor instanceof RM_Content_Field_Process_Line)) {
            $this->_lineProcessor = RM_Content_Field_Process_Line::init();
        }
        return $this->_lineProcessor;
    }

    abstract public function isValid();

    abstract public function isUnique( $excludedId = 0 );

    abstract public function format();

}
