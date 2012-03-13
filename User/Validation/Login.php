<?php
class RM_User_Validation_Login
	extends
		RM_User_Validation {

	private $_login;

	public function __construct($login) {
		$this->_login = $login;
	}

	public function getLogin() {
		return $this->_login;
	}

	public function isValid() {
		return preg_match('/^[a-z0-9\_\-\.]{3,30}$/i', $this->getLogin());
	}

	public function isUnique( $excludedId = 0 ) {
		if ($this->isValid()) {
			$user = RM_User::getByLogin( $this->getLogin() );
			if ($user instanceof RM_User) {
				if ($user->getId() !== $excludedId) {
					return false;
				}
			}
			return true;
		} else {
			return false;
		}
	}


	public function format() {
		$this->_login = $this->_getLineProcessor()->getParsedContent( $this->getLogin() );
	}

}
