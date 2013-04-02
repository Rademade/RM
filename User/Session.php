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

	const REMEMBER_TIME = 8000000;

	private function __construct() {
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

	private function _setIdUser($idUser) {
		$idUser = intval($idUser);
		$this->session->idUser = $idUser;
	}

	public function getIdUser() {
		return (int)$this->session->idUser;
	}

	public function create(RM_User_Interface $user){
		$this->_setIdUser( $user->getId() );
	}

	public static function getMyIp() {
        $IP_ServerKeys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        foreach ($IP_ServerKeys as $serverKey) {
            if ( isset( $_SERVER[ $serverKey ] ) ) {
                return $_SERVER[ $serverKey ];
            }
        }
        return '127.0.0.1';
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
		$this->session->idUser = 0;
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

}
