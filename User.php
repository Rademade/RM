<?php
/**
 * @class RM_User
 * @property int idUser
 * @property int idAvatar
 * @property int failLogins
 * @property string userPasswd
 * @property int userStatus
 * @property string userLogin
 * @property string userAddress
 * @property int userMailStatus
 * @property int idRole
 * @property string aboutUser
 * @property string userMail
 * @property string userFullName
 * @property int idCity
 * @property int phoneNumber
 */
class RM_User
	extends
		RM_Entity
	implements
		RM_Interface_Hideable,
		RM_Interface_Deletable {

	const TABLE_NAME = 'users';

	protected static $_properties = array(
		'idUser' => array(
			'id' => true,
			'type' => 'int',
			'field' => 'idUser'
		),
		'idRole' => array(
			'type' => 'int',
			'default' => RM_User_Role::USER_ACCESS_BASE_ROLE_ID
		),
		'idAvatar' => array(
			'type' => 'int'
		),
		'idCity' => array(
			'type' => 'int'
		),
		'userMail' => array(
			'type' => 'string'
		),
		'userLogin' => array(
			'type' => 'string'
		),
		'userFullName' => array(
			'type' => 'string'
		),
		'phoneNumber' => array(
			'type' => 'string'
		),
		'userAddress' => array(
			'type' => 'string'
		),
		'userPasswd' => array(
			'type' => 'string'
		),
		'aboutUser' => array(
			'type' => 'string'
		),
		'userStatus' => array(
			'type' => 'int',
			'default' => self::STATUS_SHOW
		),
		'userMailStatus' => array(
			'type' => 'int',
			'default' => self::MAIL_STATUS_NOT_VALID
		),
		'failLogins' => array(
			'type' => 'int'
		)
	);


	/**
	 * @var RM_Phone
	 */
	private $_phone;

	/**
	 * @var Application_Model_Discount
	 */
	private $_discount;

	/**
	 * @var RM_Photo
	 */
	private $_avatar;

	/**
	 * @var Application_Model_City
	 */
	private $_city;

	/**
	 * @var RM_User_Role
	 */
	private $_role;

	/**
	 * @var RM_Content_Field_Process_Text
	 */
	private $_textProcessor;
	/**
	 * @var RM_Content_Field_Process_Line
	 */
	private $_lineProcessor;

	const MAIL_STATUS_VALID = 1;
	const MAIL_STATUS_NOT_VALID = 2;

	const PASSWORD_SALT = 'ade533142f8b5123asd156e';
	const CACHE_NAME = 'user';
	const EMPTY_AVATAR_PATH = 'default-avatar.png';

	const FAIL_LOGIN_RESOLVE = 50;
	const BLOCK_TIME = 3600;
	const ERROR_USER_NOT_FOUND = 'This user not found';

	public function __construct(stdClass $data) {
		parent::__construct($data);
		$this->_phone = new RM_Phone( $data->phoneNumber );
	}

	public static function create($mail, $password) {
		$user = new self( new stdClass() );
		$user->setEmail($mail, false);
		$user->setPassword($password);
		return $user;
	}

	public function getId() {
		return $this->idUser;
	}

	public function getFailLoginCount() {
		return $this->failLogins;
	}

	public function getIdAvatar() {
		return $this->idAvatar;
	}

	public function setAvatar(RM_Photo $avatar) {
		$this->idAvatar = $avatar->getIdPhoto();
		$this->_avatar = $avatar;
	}

	public function getIdCity() {
		return $this->idCity;
	}

	/**
	 * @return RM_Photo
	 */
	public function getAvatar() {
		if (!$this->_avatar instanceof RM_Photo) {
			if ($this->getIdAvatar() === 0) {
				$this->_avatar = new RM_Photo( new RM_Compositor( array(
					'idUser' => $this->getId(),
					'photoPath' => self::EMPTY_AVATAR_PATH
				) ) );
				$this->_avatar->noSave();
			} else {
				$this->_avatar = RM_Photo::getById( $this->getIdAvatar() );
			}
		}
		return $this->_avatar;
	}

	public function setFullName($name) {
		$name = trim( $name );
		if ($name == '') {
			throw new Exception('User name is empty');
		}
		if ($this->getFullName() !== $name) {
			if (strlen($name) > 250) {
				throw new Exception('Wrong user name. Name length must be below 100 chars');
			}
			$this->userFullName = $this->_getLineProcessor()->getParsedContent( $name );
		}
	}

	public function getNameByEmail() {
		preg_match('/^(.*)\@.*$/i', $this->getEmail(), $data);
		return $data[1];
	}

	public function getFullName() {
		return $this->userFullName;
	}

	public function setPhone($phoneNumber) {
		$phone = new RM_Phone($phoneNumber);
		if (!$phone->isEmpty())
			$phone->validate();
		$this->_phone = $phone;
		$this->phoneNumber = $phoneNumber;
	}

	/**
	 * @return RM_Phone
	 */
	public function getPhone() {
		return $this->_phone;
	}

	public function setAbout($text) {
		if (strlen($text) > 240) {
			throw new Exception('Wrong about text. About text length must be below 240 chars');
		}
		$this->aboutUser = $this->_getTextProcessor()->getParsedContent( $text );
	}

	public function getAbout() {
		return $this->aboutUser;
	}

	public function getInitialAbout() {
		return $this->_getTextProcessor()->getInitialContent( $this->getAbout() );
	}

	public function getEmail() {
		return $this->userMail;
	}

	public function getIdRole() {
		return $this->idRole;
	}

	public function setRole(RM_User_Role $role) {
		$this->idRole = $role->getId();
	}

	public function getRole() {
		if (is_null($this->_role)) {
			$this->_role = RM_User_Role::getById($this->getIdRole());
		}
		return $this->_role;
	}

	public function setStatus($status) {
		$status = (int)$status;
		if (in_array($status, array(
			self::STATUS_SHOW,
			self::STATUS_HIDE,
			self::STATUS_DELETED
		))) {
			$this->userStatus = $status;
		} else {
			throw new Exception('Wrong status given');
		}
	}

	public function getStatus() {
		return $this->userStatus;
	}

	public function isEmptyPassword() {
		return $this->userPasswd === '';
	}

	private function _generatePasswordHash($password) {
		return sha1( md5( $password ) . self::PASSWORD_SALT);
	}

	public function checkPassword($password) {
		return $this->userPasswd == $this->_generatePasswordHash( $password );
	}


	public function setPassword($password) {
		if ($password === false) {//Empty password
			$this->userPasswd = '';
		} else {
			if (!$this->checkPassword($password)) {
				$length = strlen($password);
				if ($length < 6) {
					throw new Exception('Very short password');
				}
				if ($length > 50) {
					throw new Exception('Overlong password');
				}
				$this->userPasswd = $this->_generatePasswordHash( trim( $password ) );
			}
		}
	}

	public function getLogin() {
		return $this->userLogin;
	}

	private function _validateLogin(
		$login,
		RM_Exception $e = null,
		$isThrow
	) {
		if (is_null($e))
			$e = new RM_Exception();
		$validator = new Application_Model_System_User_Validation_Login( $login );
		$validator->format();
		if (!$validator->isValid()) {
			$e[] = 'Login not valid';
		} else {
			if (!$validator->isUnique( $this->getId() )) {
				$e[] = 'Such login already exists';
			}
		}
		if ($isThrow && (bool)$e->current())
			throw $e;
		return $validator->getLogin();
	}

	public function setLogin($login) {
		$login = $this->_validateLogin($login, null, true );
		$this->userLogin = $login;
	}

	public function getAddress() {
		return $this->userAddress;
	}

	public function getInitialAddress() {
		return $this->_getTextProcessor()->getInitialContent(
			$this->getAddress()
		);
	}

	public function setAddress($address) {
		$this->userAddress = $this->_getTextProcessor()->getParsedContent($address);
	}

	public function getEmailStatus() {
		return $this->userMailStatus;
	}

	public function activateEmail() {
		if (!$this->isConfirmedEmail()) {
			$this->userMailStatus = self::MAIL_STATUS_VALID;
			$this->save();
		}
	}

	public function setCity($name) {
		$this->_city = Application_Model_City::getByName( $name );
		$this->idCity = $this->_city->getId();
	}

	public function getCity() {
		if (!($this->_city instanceof Application_Model_City)) {
			$this->_city = Application_Model_City::getById( $this->getIdCity() );
		}
		return $this->_city;
	}

	public function getCityName() {
		if ($this->getCity() instanceof Application_Model_City) {
			return $this->getCity()->getName();
		} else {
			return '';
		}
	}

	private function _checkCity() {
		$this->idCity = $this->getCity()->getId();
	}

	public function save() {
		parent::save();
		$this->clean();
	}

	/**
	 * @static
	 * @param Zend_Db_Select
	 */
	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('users.userStatus != ?', self::STATUS_DELETED);
	}

	public static function getByMail($mail) {
		$mail = mb_strtolower($mail, 'utf-8');
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract */
		$select = self::_getSelect();
		$select->where('users.userMail = ?', $mail)->limit(1);
		if (($row = $db->fetchRow ( $select )) !== false) {
			return new self($row);
		} else {
			return null;
		}
	}

	public static function getByLogin($login) {
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract */
		$select = self::_getSelect();
		$select->where('users.userLogin = ?', $login)->limit(1);
		if (($row = $db->fetchRow ( $select )) !== false) {
			return new self($row);
		} else {
			return null;
		}
	}

	private function _validateEmail(
		$email,
		RM_Exception $e = null,
		$isThrow
	) {
		if (is_null($e))
			$e = new RM_Exception();
		$validator = new Application_Model_System_User_Validation_Email( $email );
		$validator->format();
		if (!$validator->isValid()) {
			$e[] = 'Email not valid';
		} else {
			if (!$validator->isUnique( $this->getId() )) {
				$e[] = 'Such email already exists';
			}
		}
		if ($isThrow && (bool)$e->current())
			throw $e;
		return $validator->getEmail();
	}

	public function setEmail($email, $needConfirm) {
		if ($this->getEmail() !== $email) {
			$email = $this->_validateEmail($email, null, true );
			$this->userMail = $email;
			if ($needConfirm) {
				$this->userMailStatus = self::MAIL_STATUS_NOT_VALID;
				$mail = new Application_Model_Mail_MailConfirm( $this );
				$mail->send( $email );
			}
		}
	}

	public function isConfirmedEmail() {
		return $this->getEmailStatus() == self::MAIL_STATUS_VALID;
	}


	public function getPurchasesAmount() {
		//TODO
		return 0;
	}

	public function getDiscount() {
		if (!($this->_discount instanceof Application_Model_Discount)) {
			$this->_discount = Application_Model_Discount::getByPrice( $this->getPurchasesAmount() );
		}
		return $this->_discount;
	}

	public function getDiscountPercent() {
		if ($this->getDiscount() instanceof Application_Model_Discount) {
			return $this->getDiscount()->getDiscountPercent();
		}
		return 0;
	}

	public static function getList() {
		$select = self::_getSelect();
		$select->join('roles', 'users.idRole = roles.idRole');
		return self::_initList($select, func_get_args());
	}

	public function validate() {
		$e = new RM_Exception();
		$this->_validateEmail($this->getEmail(), $e, false);
		$this->_validateLogin($this->getLogin(), $e, false);
		if (!$this->getPhone()->isEmpty())
			$this->getPhone()->validate();
		if ($this->getFullName() == '') {
			$e[] = 'User name not setted';
		}
		if (!($this->getCity() instanceof Application_Model_City)) {
			$e[] = 'User city not defined';
		}
		if ((bool)$e->current())
			throw $e;
		else
			return true;
	}


	public function isShow() {
		return $this->getStatus() === self::STATUS_SHOW;
	}

	public function hide() {
		if ($this->getStatus() === self::STATUS_SHOW) {
			$this->setStatus(self::STATUS_HIDE);
			$this->save();
		}
	}

	public function show() {
		if ($this->getStatus() === self::STATUS_HIDE) {
			$this->setStatus(self::STATUS_SHOW);
			$this->save();
		}
	}

	public function remove() {
		$this->setStatus(self::STATUS_DELETED);
		$this->save();
	}

	private function _getLineProcessor() {
		if (!($this->_lineProcessor instanceof RM_Content_Field_Process_Line)) {
			$this->_lineProcessor = RM_Content_Field_Process_Line::init();
		}
		return $this->_lineProcessor;
	}

	private function _getTextProcessor() {
		if (!($this->_textProcessor instanceof RM_Content_Field_Process_Text)) {
			$this->_textProcessor = RM_Content_Field_Process_Text::init();
		}
		return $this->_textProcessor;
	}

}
