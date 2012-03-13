<?php
class RM_User_Code_PasswordForgot
	extends
		RM_User_Code {

	private $_newPassword;

	const TABLE_NAME_PASSWORDS = 'forgotPasswords';

	private $_changes = array();

	public function __construct(stdClass $data) {
		$this->_newPassword = $data->newPassword;
		parent::__construct($data);
	}

	public static function create(RM_User $user) {
		$code = parent::create($user);
		/* @var $code RM_User_Code_PasswordForgot */
		$code->_generatePassword();
		return $code;
	}

	public static function getMyType() {
		return self::TYPE_FORGOT_PASSWORD;
	}

	private function _generatePassword() {
		$this->_newPassword = self::__generate( rand(6, 10) );
		$this->_changes['newPassword'] = $this->_newPassword;
	}

	public function getPassword() {
		return $this->_newPassword;
	}

	/**
	 * @static
	 * @return Zend_Db_Select
	 */
	protected static function __getSelect() {
		$select = parent::__getSelect();
		return $select->join(
			self::TABLE_NAME_PASSWORDS,
			self::TABLE_NAME . '.idCode = ' . self::TABLE_NAME_PASSWORDS . '.idCode',
			array('newPassword')
		);
	}

	public function save() {
		$insert = $this->getId() === 0;
		parent::save();
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract */
		if ($insert) {
			$db->insert(self::TABLE_NAME_PASSWORDS, array(
				'idCode' => $this->getId(),
			    'newPassword' => $this->getPassword()
            ));
			$this->_changes = array();
		} else {
			if (!empty($this->_changes)) {
				$db->update(self::TABLE_NAME, $this->_changes, 'idCode = ' . $this->getId());
				$this->_changes = array();
			}
		}
	}

}