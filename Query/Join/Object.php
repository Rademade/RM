<?php
class RM_Query_Join_Object {

    private $_joinAs;

    private $_joinObject;

    public function __construct(
        $joinAs,
        $joinObject
    ) {
        $this->_joinAs = $joinAs;
        $this->_joinObject = $joinObject;
    }

    public function getJoinArray() {
        return array(
            $this->_joinAs => $this->_joinObject
        );
    }

    public function __toString() {
        return $this->_joinAs;
    }

}