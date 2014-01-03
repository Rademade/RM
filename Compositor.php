<?php
class RM_Compositor extends stdClass {
	
	protected $composite = array();

	public function __construct() {
		foreach (func_get_args() as $obj) {
			$this->composite[] = $this->_prepare($obj);
		}
	}

	private function _prepare($obj) {
		if (is_array($obj)) {
			$obj = (object)$obj;
		}
		return $obj;
	}
	
	public function add($obj) {
		$this->composite[] = $this->_prepare($obj);
	}

	public function __isset($name) {
		foreach($this->composite as &$object) {
			if (isset($object->$name)) {
				return true;
			}
		}
		return false;
	}

	public function __set($name, $value)  {
		foreach($this->composite as &$object) {
			if (isset($object->$name)) {
				$object->$name = $value;
				return ;
			}
		}
        $this->composite[] = (object)array($name => $value);
	}
	
	public function __get($name) {
		foreach($this->composite as $object) {
			if (isset($object->$name)) {
				return $object->$name;
				break;
			}
		}
	}

}