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

    const REX_LANG_URL = '([a-z]{2,4})';

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

    public static function getByIsoName($isoName) {
        $key = md5( $isoName );
        if (is_null($item = static::_getStorage()->getData($key))) {
            $select = static::_getSelect();
            $select->where('isoName = ?', $isoName);
            $item = static::_initItem($select);
            static::_getStorage()->setData($item, $key);
        }
        return $item;
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

    //RM_TODO cache
    public static function getRegexForLangUrls() {
        $langUrls = array();
        foreach (RM_Lang::getList() as $lang) {
            /* @var RM_Lang $lang */
            $langUrls[] = $lang->getUrl();
        }
        return join('|', $langUrls);
    }

	public function getIsoName() {
		return $this->isoName;
	}

    public function getHtmlName() {
        return str_replace('_', '-', $this->getIsoName());
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
	
	public function setUrl($url) {
		$url = $this->langUrl = stripslashes(trim($url));
        if (preg_match(
            '/^' . self::REX_LANG_URL . '$/',
            $url,
            $data
        )) {
            $this->langUrl = $url;
        } else {
            throw new Exception('Lang url must be 2-4 characters long and contains only letters and digits');
        }
	}

	public function getUrl() {
		return $this->langUrl;
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

