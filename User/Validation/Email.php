<?php
class RM_User_Validation_Email
	extends
		RM_User_Validation {

	private $_email;

	public function __construct($email) {
		$this->_email = $email;
	}

	public function getEmail() {
		return $this->_email;
	}

	public function isValid() {
		$validator = new Zend_Validate_EmailAddress(array(
			 'allow' => Zend_Validate_Hostname::ALLOW_DNS
		));
		return $validator->isValid( $this->getEmail() );
	}

	public function isUnique( $excludedId = 0 ) {
		if ($this->isValid()) {
            $profileClass = RM_Dependencies::getInstance()->userProfile;
			$user = $profileClass::getByEmail( $this->getEmail() );
			if ($user instanceof $profileClass) {
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
		$this->_email = mb_strtolower(
			$this->_getLineProcessor()->getParsedContent( $this->getEmail() ),
			'utf-8'
		);
	}

}
