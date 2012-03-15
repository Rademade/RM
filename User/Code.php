<?php
abstract class RM_User_Code {
	
	private $_idCode;
	private $_idUser;
	private $_activationCode;
	private $_codeStatus;
	private $_codeType;
	private $_makeDate;
	
	private $_changes = array();
	
	const STATUS_WAIT = 1;
	const STATUS_ACTIVATE = 2;
	const STATUS_DROPED = 3;

	const TYPE_FORGOT_PASSWORD = 1;
	const TYPE_EMAIL_CONFIRM = 2;

	const TABLE_NAME = 'activationCodes';

	public function __construct(stdClass $data) {
		$this->_idCode = (int)$data->idCode;
		$this->_idUser = (int)$data->idUser;
		$this->_activationCode = $data->activationCode;
		$this->_codeStatus = (int)$data->codeStatus;
		$this->_codeType = (int)$data->codeType;
		$this->_makeDate = (int)$data->makeDate;
	}
	
	public function getId() {
		return $this->_idCode;
	}

	public function getType() {
		return $this->_codeType;
	}

	public function getCode() {
		return $this->_activationCode;
	}
	
	public function getIdUser() {
		return $this->_idUser;
	}
	
	public static function create(RM_User $user) {
		return new static( new RM_Compositor( array(
			'idUser' => $user->getId(),
		    'activationCode' =>self::_generateCode(),
			'codeStatus' => self::STATUS_WAIT,
			'codeType' => static::getMyType(),
			'makeDate' => time()
        ) ) );
	}

	/**
	 * @static
	 * @return Zend_Db_Select
	 */
	protected static function __getSelect() {
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract */
		$select = $db->select();
		/* @var $select Zend_Db_Select */
		$select->from(self::TABLE_NAME, '*');
		return $select->where('codeStatus != ?', self::STATUS_DROPED);
	}

	public static function getByCode($code) {
		$select = static::__getSelect();
		$select->where('activationCode = ?', mb_strtoupper(trim($code), 'UTF-8'));
		$select->where('codeType = ?', static::getMyType());
		$select->limit(1);
		if (($data = Zend_Registry::get('db')->fetchRow($select)) !== false) {
			return new static($data);
		} else {
			return false;
		}
	}

	public function getDate() {
		return $this->_makeDate;
	}

	public function isUsed(){
		return $this->getStatus() === self::STATUS_ACTIVATE;
	}

	public function getStatus() {
		return $this->_codeStatus;
	}

	public function setStatus($status) {
		$status = (int)$status;
		if ($this->getStatus() !== $status) {
			$this->_codeStatus = $status;
			$this->_changes['codeStatus'] = $status;
		}
	}

	public function setUsed() {
		$this->setStatus( self::STATUS_ACTIVATE );
		$this->save();
	}

	public function remove() {
		$this->setStatus( self::STATUS_DROPED );
		$this->save();
	}

	protected static function __generate($length) {
		$key = '';
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		for ($p = 0; $p < $length; $p++) {
			$char = $characters[mt_rand(0, strlen($characters) - 1)];
	        $key .= (rand(0, 1)) ? mb_strtolower($char, 'UTF-8') : $char;
	    }
		return $key;
	}

	private static function _generateCode() {
		$key = self::__generate(rand(20, 30));
		if (static::getByCode($key) instanceof self) {
			return static::_generateCode();
		}
		return $key;
	}
	
	public static function dropUserCode($idUser) {
		$idUser = (int)$idUser;
		$conditions = new RM_Query_Where();
		$conditions->add('idUser', RM_Query_Where::EXACTLY, $idUser);
		foreach (static::getList() as $code) {
			$code->remove();
		}

	}

	/**
	 * @return Application_Model_System_User_Code[]
	 */
	public static function getList() {
		$select = static::__getSelect();
		$select->where('codeType = ?', static::getMyType());
		$list = RM_Query_Exec::select($select, func_get_args());
		foreach ($list as &$code) {
			$code = new static($code);
		}
		return $list;
	}

	public function save() {
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract */
		if ($this->_idCode === 0) {
			static::dropUserCode($this->getIdUser());
			$db->insert(self::TABLE_NAME, array(
				 'idUser' => $this->_idUser,
				 'activationCode' => $this->getCode(),
				 'codeStatus' => $this->getStatus(),
				 'codeType' => $this->getType(),
				 'makeDate' => $this->getDate()
			));
			$this->_idCode = $db->lastInsertId();
		} else {
			if (!empty($this->_changes)) {
				$db->update(self::TABLE_NAME, $this->_changes, 'idCode = ' . $this->getId());
			}
		}
	}
	
}
