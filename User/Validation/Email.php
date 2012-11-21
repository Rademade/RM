<?php
class RM_User_Validation_Email
	extends
		RM_User_Validation {

    const EMAIL_EXCEPTION_CODE = 100;

    const CACHE_NAME = 'emailValidation';

	private $_email;

	public function __construct($email) {
		$this->_email = $email;
	}

	public function getEmail() {
		return $this->_email;
	}

	public function isValid() {
		$validator = new Zend_Validate_EmailAddress(array(
	        'allow' => Zend_Validate_Hostname::ALLOW_DNS,
		));
		return $validator->isValid( $this->getEmail() );
	}

    public function isValidRemote() {
        return $this->isValid() && $this->_checkRemoteEmail( $this->getEmail() );
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

    private function _checkRemoteEmail($email) {
        $emailHash = md5( $email );//generate hash
        /* @var Zend_Cache_Manager $cachemanager */
        $cachemanager = Zend_Registry::get('cachemanager');
        /* @var Zend_Cache_Core $cache */
        $cache = $cachemanager->getCache( self::CACHE_NAME );
        if ( ($status = $cache->load( $emailHash )) === false ) {
            $browser = new RM_System_Browser();
            $browser->setMaxWaiting( 30 );
            $browser->setPostData( array(
                'name' => 'email',
                'cmd' => $email
            ) );
            $result = $browser->download( 'http://domw.net/data.php' );
            $status = preg_match('/OK\</', $result);
            $cache->save( $status );
        }
        return $status === 1;
    }

}
