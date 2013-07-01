<?php
class RM_Content
	extends
		RM_Entity
	implements
		RM_Interface_Deletable {

	const CACHE_NAME = 'content';
	const AUTO_CACHE = false;

	const TABLE_NAME = 'contents';

	protected static $_properties = array(
		'idContent' => array(
			'id' => true,
			'type' => 'int'
		),
		'idDefaultLang' => array(
			'default' => 0,
			'type' => 'int'
		),
		'contentStatus' => array(
			'default' => self::STATUS_UNDELETED,
			'type' => 'int'
		)
 	);

	private $_contentLangs = array();
	private $_loadedContentLangs = false;

	private $_settedLangs = array();

    /**
     * @var RM_Entity_Worker_Data
     */
    private $_dataWorker;
    /**
     * @var RM_Entity_Worker_Cache
     */
    protected $_cacheWorker;

    public function __construct(stdClass $data) {
        $this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
        $this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
    }

	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('contents.contentStatus != ?', self::STATUS_DELETED);
	}

    public function getId() {
        return $this->_dataWorker->_getKey()->getValue();
    }

	private function getIdDefaultLang() {
		return $this->_dataWorker->getValue('idDefaultLang');
	}

	public function setDefaultLang(RM_Lang $lang) {
		if (!$this->getContentLang($lang)) {
			throw new Exception('Such content in content manager not exist');
		}
        $this->_dataWorker->setValue('idDefaultLang', $lang->getId());
	}

	public function isLoadedContentLangs() {
		return $this->_loadedContentLangs;
	}

	private function loadContentLangs() {
		if (!$this->isLoadedContentLangs()) {
			$this->_loadedContentLangs = true;
			$where = new RM_Query_Where();
			$where->add('idContent', RM_Query_Where::EXACTLY, (int)$this->getId());
			foreach (RM_Content_Lang::getList( $where ) as $contentLang) {
				/* @var $contentLang RM_Content_Lang */
				if (!isset($this->_contentLangs[ $contentLang->getIdLang() ])) {
					$this->_contentLangs[ $contentLang->getIdLang() ] = $contentLang;
				}
			}
		}
	}

    /**
     * @return RM_Content
     */
    public static function create() {
		$content = new static( new RM_Compositor( array(
            'idLang' => RM_Lang::getCurrent()->getId()
        ) ) );
		$content->addContentLang( RM_Lang::getCurrent() );
		return $content;
	}

	/**
	 * Get default content lang for this content manager
	 * Default idLang get form lang or custom set in content manager
	 *
	 * @name getDefaultContentLang
	 * @access public
	 * @return RM_Content_Lang
	 */
	public function getDefaultContentLang() {
		return $this->getContentLangByLangId( $this->getDefaultIdLang() );
	}

	public function getDefaultIdLang() {
		if ($this->getIdDefaultLang() === 0) {
			return RM_Lang::getDefault()->getId();
		} else {
			return $this->getIdDefaultLang();
		}
	}

	public function isDefaultContentLang(RM_Lang $lang) {
		return $this->getDefaultIdLang() === $lang->getId();
	}

	/**
	 * Get current content lang from this contnent manager
	 * About current lang know Lang
	 *
	 * @name getCurrentContentLang
	 * @access public
	 * @return RM_Content_Lang
	 */
	public function getCurrentContentLang() {
		$currentLangId = RM_Lang::getCurrent()->getId();
		$contentLang = $this->getContentLangByLangId( $currentLangId );
		if ( !$contentLang instanceof RM_Content_Lang || $contentLang->getId() === 0 ) {
			$contentLang = $this->getDefaultContentLang();
		}
		return $contentLang;
	}

    /**
     * @param RM_Lang $lang
     * @return \RM_Content_Lang
     */
	public function getContentLang(RM_Lang $lang) {
		return $this->getContentLangByLangId( $lang->getId() );
	}

	/**
	 * @param RM_Lang $lang
	 * @return RM_Content_Lang
	 */
	public function addContentLang(RM_Lang $lang) {
		$idLang = $lang->getId();
		if (!isset( $this->_contentLangs[ $idLang ] )) {
			$this->_contentLangs[ $idLang ] = RM_Content_Lang::getByContent($this->getId(), $idLang);
			$this->_settedLangs[] = $idLang;
		}
		return $this->getContentLangByLangId( $idLang );
	}

	public function removeContentLang(RM_Lang $lang) {
		$idLang = $lang->getId();
		$this->getContentLangByLangId( $idLang )->remove();
		unset( $this->_contentLangs[ $idLang ] );
	}

    /**
     * @param $idLang
     * @return RM_Content_Lang
     */
	private function getContentLangByLangId($idLang) {
		if ( isset( $this->_contentLangs[ $idLang ] ) ) {
			return $this->_contentLangs[ $idLang ];
		} else {
			if ($this->isLoadedContentLangs()) {
				return false;
			} else {
				$contentLang = RM_Content_Lang::getByContent($this->getId(), $idLang);
				$this->_contentLangs[ $idLang ] = $contentLang;
				return $contentLang;
			}
		}
	}

	/**
	 * @return RM_Content_Lang[]
	 */
	public function getAllContentLangs() {
		$this->loadContentLangs();
		return $this->_contentLangs;
	}

    /**
     * @return RM_Content_Lang[]
     */
    public function getContentLangs() {
		$contentLangs = array();
		foreach ($this->getAllContentLangs() as $contentLang) {
			/* @var $contentLang RM_Content_Lang */
			if ($contentLang->getId() !== 0) {
				$contentLangs[] = $contentLang;
			}
		}
		return $contentLangs;
	}

	private function _saveContent() {
		foreach ($this->_contentLangs as $contentLang) {
			/* @var $contentLang RM_Content_Lang */
			$contentLang->setIdContent( $this->getId() );
            $contentLang->save();
		}
	}

	public function removeUnsetedLangs() {
		foreach ($this->getAllContentLangs() as $lang) {
			/* @var $lang RM_Lang */
			if ( !in_array($lang->getId(), $this->_settedLangs) ) {
                $rmLang = RM_Lang::getById( $lang->getId() );
                if ($rmLang instanceof RM_Lang) {
                    $this->removeContentLang( $rmLang );;
                }
			}
		}
	}

	public function __cachePrepare() {
		foreach ($this->getAllContentLangs() as $contentLang) {
			$contentLang->loadFields();
		}
	}

	public function save() {
        $this->_dataWorker->save();
        $this->_saveContent();
        $this->__refreshCache();
        return $this;
	}

	public function remove() {
		$this->_dataWorker->setValue('contentStatus', self::STATUS_DELETED);
		$this->save();
		$this->__cleanCache();
	}

}