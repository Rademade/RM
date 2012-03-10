<?php
class RM_Phone {

	private $_phone;
	
	public function __construct($phone) {
		$this->_phone = $phone;
	}
	
	public function setPhoneNumber($phoneNumber) {
		$this->_phone = trim( $phoneNumber );
		$this->validate();
	}
	
	public function clear() {
		$this->_phone = '';
	}

	public function getPhoneNumber() {
		return $this->_phone;
	}
	
	public function validate() {
		if (!preg_match('/^\+?[0-9]{8,14}$/', $this->getPhoneNumber()))
			throw new Exception('Wrong phone format');
		return true;
	}
	
	public function isEmpty() {
		return $this->getPhoneNumber() == '';
	}
	
	public function getFormatedPhoneNumber() {
		return str_replace(array(
			'+'
		), array(
			''
		), $this->getPhoneNumber());
	}
	
}