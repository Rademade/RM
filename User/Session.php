<?php
class RM_User_Session {

	/**
	 * @var Zend_Session_Namespace
	 */
	protected $session;

	/**
	 * @var RM_User_Interface
	 */
	protected  $_user;

	protected static $_self;

	const REMEMBER_TIME = 5184000;

    const IP_ADDRESS_REGEX = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';

    const DEFAULT_IP = '127.0.0.1';

	protected function __construct() {
		$this->session = new Zend_Session_Namespace ("User");
	}

    public function __set($name, $value) {
        if ($name == 'idUser') {
            throw new Exception('Property idUser is private');
        }
        $this->session->{$name} = $value;
    }

    public function __get($name) {
        return $this->session->{$name};
    }

    public function __isset($name) {
        return $this->session->__isset( $name );
    }

	/**
	 * @static
	 * @return RM_User_Session
	 */
	public static function getInstance() {
		if (!(static::$_self instanceof static)) {
            static::$_self = new static();
		}
		return static::$_self;
	}

	public static function getMyIp() {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && self::testIp($ip = $_SERVER[$key])) {
                return $ip;
            }
        }
        return self::DEFAULT_IP;
	}

    public static function testIp($ip) {
        return preg_match(self::IP_ADDRESS_REGEX, $ip);
    }

    public function remember() {
        Zend_Session::rememberMe(self::REMEMBER_TIME);
    }

    /**
     * @deprecated
     */
	public function remeber() {
        $this->remember();
	}

	public function logout() {
        Zend_Session::rememberUntil(0);
        $this->session->idUser = 0;
		$this->session->idOrder = null;
	}

	/**
	 * @return RM_User_Interface|null
	 */
	public function getUser() {
		if ($this->getIdUser() !== 0) {
            $userClass = RM_Dependencies::getInstance()->userClass;
            /* @var RM_Entity $userClass */
            $this->_user = $userClass::getById($this->getIdUser());
			if ($this->_user instanceof $userClass && $this->_user->isShow()) {
				return $this->_user;
			} else {
				$this->logout();
			}
		}
        return null;
	}

	public function isLogin() {
        $userClass = RM_Dependencies::getInstance()->userClass;
		return $this->getUser() instanceof $userClass;
	}

    public function getIdUser() {
		return (int)$this->session->idUser;
	}

    public function create(RM_User_Interface $user){
		$this->_setIdUser( $user->getId() );
	}

    private function _setIdUser($idUser) {
        $idUser = intval($idUser);
        $this->session->idUser = $idUser;
    }

}
