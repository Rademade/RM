<?php
/**
* @property int id
* @property int codeType
* @property string activationCode
* @property int idUser
* @property int codeStatus
* @property int makeDate
*/
abstract class RM_User_Code
	extends
		RM_Entity {

	/**
	 * @var RM_Entity_Worker_Data
	 */
	private $_entityWorker;

	const TABLE_NAME = 'activationCodes';

	protected static $_properties = array(
		'id' => array(
			'id' => true,
			'field' => 'idCode',
			'type' => 'int'
		),
		'idUser' => array(
			'type' => 'int'
		),
		'activationCode' => array(
			'type' => 'string'
		),
		'codeStatus' => array(
			'default' => self::STATUS_WAIT,
			'type' => 'int'
		),
		'codeType' => array(
			'type' => 'int'
		),
		'makeDate' => array(
			'type' => 'int'
		)
	);

	const STATUS_WAIT = 1;
	const STATUS_ACTIVATE = 2;
	const STATUS_DROPED = 3;

	const TYPE_FORGOT_PASSWORD = 1;
	const TYPE_EMAIL_CONFIRM = 2;

	public function __construct($data) {
		$this->_entityWorker = new RM_Entity_Worker_Data(get_class(), $data);
	}

	public function __get($name) {
		$val = $this->_entityWorker->getValue($name);
		if (is_null($val)) {
			throw new Exception("Try to get unexpected attribute {$name}");
		} else {
			return $val;
		}
	}

	public function __set($name, $value) {
		if (is_null($this->_entityWorker->setValue($name, $value))) {
			throw new Exception("Try to set unexpected attribute {$name}");
		}
	}

	public function save() {
		if ($this->_entityWorker->save());
	}

	public function getId() {
		return $this->id;
	}

	public function getType() {
		return $this->codeType;
	}

	public function getCode() {
		return $this->activationCode;
	}
	
	public function getIdUser() {
		return $this->idUser;
	}
	
	public static function create(RM_User $user) {
		return new static( new RM_Compositor( array(
			'idUser' => $user->getId(),
		    'activationCode' =>self::_generateCode(),
			'codeType' => static::getMyType(),
			'makeDate' => time()
        ) ) );
	}

	public static function getMyType(){
		throw new Exception('You must redefine this method');
	}

	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('codeStatus != ?', self::STATUS_DROPED);
		$select->where('codeType = ?', static::getMyType());
	}

	public static function getByCode($code) {
		$select = self::_getSelect();
		$select->where('activationCode = ?', mb_strtoupper(trim($code), 'UTF-8'));
		return self::_initItem($select);
	}

	public function getDate() {
		return $this->makeDate;
	}

	public function isUsed(){
		return $this->getStatus() === self::STATUS_ACTIVATE;
	}

	public function getStatus() {
		return $this->codeStatus;
	}

	public function setStatus($status) {
		$this->codeStatus = (int)$status;
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
			/* @var $code RM_User_Code */
			$code->remove();
		}

	}

}
