<?php
class RM_Exception
	extends
		Exception
	implements
		ArrayAccess,
		Iterator
{

	protected $_list = array();


    public static function initArray(array $errors) {
        $rmException = new self();
        foreach ($errors as $error) {
            $rmException->_list[] = $error['text'];
        }
        return $rmException;
    }

	public function __construct() {
	}

    public function hasError() {
        return sizeof( $this->_list ) > 0;
    }

	public function offsetExists($index) {
	  return isset($this->_list[$index]);
	}

	public function offsetGet($index) {
	  return $this->_list[$index];
	}

	public function offsetSet($index, $value) {
		if (isset($index)) {
			$this->_list[$index] = $value;
		} else {
			$this->_list[] = $value;
		}
	}

	public function offsetUnset($index) {
	  unset($this->_list[$index]);
	}

	public function current() {
	  return current($this->_list);
	}

	public function key() {
	  return key($this->_list);
	}

	public function next() {
	  return next($this->_list);
	}

	public function rewind() {
	  return reset($this->_list);
	}

	public function valid() {
	  return (bool) $this->current();
	}

	public function getMessages(){
		return $this->_list;
	}

}