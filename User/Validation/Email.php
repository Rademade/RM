<?php
class RM_User_Validation_Email
	extends
		RM_User_Validation {

    const CACHE_NAME = 'emailValidation';

    const EMAIL_EXCEPTION_CODE = 100;

	private $_email;

	public function __construct($email) {
		$this->_email = $email;
	}

	public function getEmail() {
		return $this->_email;
	}

	public function isValid() {
        return preg_match('/.+@.+\..+/i', $this->getEmail());
	}

    public function isValidRemote() {
        return $this->_validateWithOnlineService($this->getEmail());
    }

	public function isUnique($excludedId = 0) {
        $profileClass = RM_Dependencies::getInstance()->userProfile;
        $user = $profileClass::getByEmail( $this->getEmail() );
        return !$user instanceof $profileClass || $user->getId() !== $excludedId;
	}


	public function format() {
		$this->_email = mb_strtolower(
			$this->_getLineProcessor()->getParsedContent( $this->getEmail() ),
			'utf-8'
		);
	}

    private function _validateWithOnlineService($email) {
        $emailHash = md5( $email ); //generate hash
        /* @var Zend_Cache_Manager $cachemanager */
        $cachemanager = Zend_Registry::get('cachemanager');
        /* @var Zend_Cache_Core $cache */
        $cache = $cachemanager->getCache( self::CACHE_NAME );
        if ( ($status = $cache->load( $emailHash )) === false ) {
            $browser = new RM_System_Browser();
            $browser->setMaxWaiting( 30 );
            $browser->setPostData([
                'name' => 'email',
                'cmd' => $email
            ]);
            $result = $browser->download( 'http://domw.net/data.php' );
            $status = preg_match('/OK\</', $result);
            $cache->save( $status );
        }
        return $status === 1;
    }

}
