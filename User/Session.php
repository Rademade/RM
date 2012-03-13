<?php
class RM_User_Session {

	/**
	 * @var Zend_Session_Namespace
	 */
	private $session;

	/**
	 * @var RM_User
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

	public function create(RM_User &$user){
		$this->_setIdUser( $user->getId() );
	}
	
	public function getMyIp() {
		return getenv('REMOTE_ADDR');
	}

	public function remeber() {
		Zend_Session::rememberMe(self::REMEMBER_TIME);
	}

	public function logout() {
		$this->session->idUser = 0;
	}

	/**
	 * @return RM_User
	 */
	public function getUser() {
		if ($this->getIdUser() !== 0) {
			$this->_user = RM_User::getById($this->getIdUser());
			if ($this->_user instanceof RM_User && $this->_user->isShow()) {
				return $this->_user;
			} else {
				$this->logout();
				return false;
			}
		} else {
			return false;
		}
	}

	public function isLogin() {
		return $this->getUser() instanceof RM_User;
	}
	
}
