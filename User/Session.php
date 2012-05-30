<?php
class RM_User_Session {

	/**
	 * @var Zend_Session_Namespace
	 */
	private $session;

	/**
	 * @var RM_User_Interface
	 */
	private $_user;

	private static $_self;

	const REMEMBER_TIME = 8000000;

	private function __construct() {
		$this->session = new Zend_Session_Namespace ("User");
	}

	/**
	 * @static
	 * @return RM_User_Session
	 */
	public static function getInstance() {
		if (!(self::$_self instanceof self)) {
			self::$_self = new self();
		}
		return self::$_self;
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

	public function getMyIp() {
		return getenv('REMOTE_ADDR');
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
