<?php
trait RM_Trait_AssocIterator {

    /**
     * @var array
     */
    protected $_iteratorArray = [];

    /**
     * Category id current key
     * @var int
     */
    protected $_key = null;

    /**
     * Current index
     * @var int
     */
    protected $_currentPosition = 0;


    public function __construct() {
        $this->rewind();
    }

    public function rewind() {
        $this->_currentPosition = 0;
        $keys = array_keys($this->_iteratorArray);
        $this->_key = empty($keys) ? null : $keys[0];
    }

    public function current() {
        return $this->_iteratorArray[ $this->_key ];
    }

    public function key() {
        return $this->_key;
    }

    public function next() {
        ++$this->_currentPosition;
        if ($this->valid()) {
            $this->_key = array_keys($this->_iteratorArray)[ $this->_currentPosition ];
        } else {
            $this->_key = null;
            return false;
        }
    }

    public function valid() {
        return $this->_currentPosition < sizeof($this->_iteratorArray);
    }



}