<?php
class RM_Phone {

    public static $validationRegex = '/^\+?[0-9]{8,14}$/';

	private $_phone;

    public static function clearPhoneNumber($phone) {
        return preg_replace('/\D/', '', $phone);
    }

	public function __construct($phone) {
		$this->setPhoneNumber($phone, false);
	}
	
	public function setPhoneNumber($phoneNumber, $validate = true) {
		$this->_phone = self::clearPhoneNumber($phoneNumber);
        if ($validate) $this->validate();
	}
	
	public function clear() {
		$this->_phone = '';
	}

	public function getPhoneNumber() {
		return $this->_phone;
	}

    public function getNumberWithCode($code) {
        return preg_replace('/.*(\d{10})/', '+' . $code . '${1}', $this->getPhoneNumber());
    }
	
	public function validate() {
		if (!preg_match(static::$validationRegex, $this->getPhoneNumber()))
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

    public function getPrettyPhoneFormat() {
        return preg_replace(
            '/^(\+[0-9]{2})?([0-9]{3})([0-9]*)([0-9]{2})([0-9]{2})$/',
            '${1} (${2}) ${3} ${4} ${5}',
            $this->getPhoneNumber()
        );
    }

}