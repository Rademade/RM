<?php
class RM_Routing_DefaultParams {
	
	private $_params;
	
	public function __construct($serializedParams = '{}') {
		$this->_params = json_decode($serializedParams);
	}

	public function __set($param, $val) {
		$this->_params->{$param} = $val;
	}

	public function __get($param) {
		if (isset($this->_params->{$param})) {
			return $this->_params->{$param};
		} else {
			return false;
		}
	}

	public function getParams() {
		return (array)$this->_params;	
	}
	
	public function __toString() {
		return json_encode($this->_params);
	}

}