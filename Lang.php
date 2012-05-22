<?php
/**
* @property int idLang
* @property int idPhoto
* @property mixed isoName
* @property mixed langName
* @property mixed langUrl
* @property mixed defaultStatus
* @property mixed langStatus
*/
class RM_Lang
	extends
		RM_Entity
	implements
		RM_Interface_Hideable,
		RM_Interface_Deletable {

	const CACHE_NAME = 'lang';

	const TABLE_NAME = 'langs';

	protected static $_properties = array(
		'idLang' => array(
			'id' => true,
			'type' => 'int'
		),
		'idPhoto' => array(
			'type' => 'int'
		),
		'isoName' => array(
			'type' => 'string'
		),
		'langName' => array(
			'type' => 'string'
		),
		'langUrl' => array(
			'type' => 'string'
		),
		'defaultStatus' => array(
			'type' => 'int'
		),
		'langStatus' => array(
			'default' => self::STATUS_HIDE,
			'type' => 'int'
		)
	);

	private static $current; 
	private static $default;
	
	/**
	 * @var RM_Photo
	 */
	private $photo = null;

	public static function create(
		$isoName,
		$langName
	){
		$lang = new self();
		$lang->setIsoName( $isoName );
		$lang->setName( $langName );
		return $lang;
	}
	
	public function getPhoto() {
		if (is_null($this->photo)) {
			try {
				$this->photo = RM_Photo::getById($this->getIdPhoto());
			} catch(Exception $e) {
				$this->photo = RM_Photo::getEmpty();
			}
		}
		return $this->photo;
	}
	
	public function getIdPhoto() {
		return $this->idPhoto;
	}
	
	public function setPhoto(RM_Photo $photo) {
		$this->photo = $photo;
		$this->idPhoto = $photo->getIdPhoto();
	}

	/**
	 * @param Zend_Db_Select
	 */
	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('langStatus != ?', self::STATUS_DELETED);
	}

	public function __getCacheTags() {
		return array(
			'lang_' . $this->getId()
		);
	}

    /**
     * @static
     * @return RM_Lang
     */
	public static function getDefault() {
		if (!(self::$default instanceof RM_Lang)) {
			$select = self::_getSelect()->where('defaultStatus = 1');
			self::$default = self::_initItem($select);
		}
		return self::$default;
	}

    public static function getByUrl($url) {
        $select = self::_getSelect();
        $select->where('langUrl = ?', $url);
        return self::_initItem($select);
    }

	public function setAsCurrent() {
		self::$current = $this;
	}

    /**
     * @static
     * @return RM_Lang
     */
	public static function getCurrent() {
		if (!(self::$current instanceof RM_Lang)) {
			self::$current = self::getDefault();
		}
		return self::$current;
	}

	public function getIsoName() {
		return $this->isoName;
	}
	
	public function setIsoName($iso) {
		$iso = trim($iso);
		if (strlen($iso) < 3) {
			throw new Exception('ISO NAME IS WRONG');
		}
		$this->isoName = $iso;
	}
	
	public function getName() {
		return $this->langName;
	}

	public function setName($name) {
		if (strlen($name) < 1) {
			throw new Exception('NAME NOT ARE TO SHORT');
		}
		$this->langName = $name;
	}
	
	/**
	 * @name validate
	 * @return array $errors
	 */
	private function validate() {
		if ($this->getIdPhoto() === 0) {
			throw new Exception( 'Photo no uploaded' );
		}
		if ($this->getName() === '') {
			throw new Exception( 'Language name not defined' );
		}
		if ($this->getIsoName() === '') {
			throw new Exception( 'ISO name not defined' );
		}
		if ($this->getUrl() === '') {
			throw new Exception( 'Url not defined' );
		} else {
			if (!$this->checkUnique()) {
				throw new Exception( 'This url is already used' );
			}
		}
	}

	public function setUrl($url) {
		if ($this->getUrl() !== $url) {
			$this->langUrl = stripslashes(trim($url));
		}
	}

	public function getUrl() {
		return $this->langUrl;
	}

	private function checkUnique() {
		$select = self::getDb()->select()
			->from('langs',array(
				'count'=>'COUNT(idLang)'
			))
			->where('langUrl = ? ', $this->getUrl())
			->where('langStatus != ?', self::STATUS_DELETED)
			->where('idLang != ? ', $this->getId());
		if (self::getDb()->fetchRow($select)->count === 0) {
			return true;
		} else {
			throw new Exception('URL ' . $this->getUrl() . ' NOT EXIST');
		}
	}

	public function isDefault() {
		return ($this->defaultStatus === 1);
	}
	
	private function removeDefault() {
		if ($this->isDefault()) {
			$this->defaultStatus = 0;
			$this->save();
		}
	}

	public function makeDefault() {
		if (!$this->isDefault()) {
			self::getDefault()->removeDefault();
			$this->defaultStatus = 1;
		}
		$this->save();
	}
	
	public function getStatus() {
		return $this->langStatus;
	}
	
	public function setStatus($status) {
		$status = (int)$status;
		if (in_array($status, array(
			self::STATUS_HIDE,
			self::STATUS_SHOW,
			self::STATUS_DELETED
		))) {
			$this->langStatus = $status;
		} else {
			throw new Exception('WRONG STATUS');
		}
	}

	public function isShow() {
		return $this->getStatus() === self::STATUS_SHOW;
	}

	public function show() {
		if (!$this->isShow()) {
			$this->setStatus(self::STATUS_SHOW);
			$this->save();
		}
	}
	
	public function hide() {
		if ($this->isShow()) {
			$this->setStatus(self::STATUS_HIDE);
			$this->save();
		}
	}
	
	public function remove() {
		if (!$this->isDefault()) {
			$this->setStatus(self::STATUS_DELETED);
			//TODO need to delete content langs
			$this->save();
			$this->__cleanCache();
		}
	}

}

