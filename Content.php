<?php 
class RM_Content {

	private $_idContent;
	private $_idDefaultLang = 0;
	private $_contentLangs = array();
	private $_loadedContentLangs = false;

	private $_settedLangs = array();
	private $_changes = array();

	const CACHE_NAME = 'content';

	const STATUS_SHOW = 1;
	const STATUS_DROP = 2;
	
	public function __construct(
		$idContent,
		$idDefaultLang
	) {
		$this->_idContent = (int)$idContent;
		$this->_idDefaultLang = (int)$idDefaultLang;
	}
	
	public function getId() {
		return (int)$this->_idContent;
	}
	
	private function getIdDefaultLang() {
		return $this->_idDefaultLang;
	}
	
	public function setDefaultLang(RM_Lang $lang) {
		if (!$this->getContentLang($lang)) {
			throw new Exception('Such content in content manager not exist');
		}
		if ($this->getIdDefaultLang() !== $lang->getId()) {
			$this->_changes['idDefaultLang'] = $lang->getId();
			$this->_idDefaultLang = $lang->getId();
		}
	}
	
	public function isLoadedContentLangs() {
		return $this->_loadedContentLangs;
	}
	
	private function loadContentLangs() {
		if (!$this->isLoadedContentLangs()) {
			$this->_loadedContentLangs = true;
			foreach (
				RM_Content_Lang::getList( $this->getId() ) as
				$contentLang
			) {
				if (!isset($this->_contentLangs[ $contentLang->getIdLang() ])) {
					$this->_contentLangs[ $contentLang->getIdLang() ] = $contentLang;
				}
			}
		}
	}
	
	public static function create() {
		$content = new self(0, RM_Lang::getCurrent()->getId());
		$content->addContentLang( RM_Lang::getCurrent() );
		return $content;
	}

    /**
     * @static
     * @param $idContent
     * @return RM_Content|bool
     * @throws Exception
     */
	public static function getById($idContent) {
		if ( ($content = self::loadFromCache( $idContent )) !== false )
			return $content;
		$idContent = (int)$idContent;
		$db = Zend_Registry::get('db');
        /* @var $db Zend_Db_Adapter_Abstract */
		$select = $db->select()->from('contents', array('idDefaultLang'));
		$select->where('idContent = ?', $idContent);
		$select->where('contentStatus = ?', self::STATUS_SHOW);
		$select->limit(1);
		if (($data = $db->fetchRow($select)) !== false) {
			$content = new self($idContent, $data->idDefaultLang);
			$content->cache();
			return $content;
		} else {
			throw new Exception('Content with id' . $idContent . ' not exist');
		}
	}
	
	/**
	 * Get default content lang for this contnent manager
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
		if ( $contentLang->getId() === 0 ) {
			$contentLang = $this->getDefaultContentLang();
		}
		return $contentLang;
	}
	
	/**
	 * @return RM_Content_Lang
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
	 * @return Application_Model_System_Content_Lang[]
	 */
	public function getAllContentLangs() {
		$this->loadContentLangs();
		return $this->_contentLangs;
	}
	
	public function getContentLangs() {
		$contentLangs = array();
		foreach ($this->getAllContentLangs() as $contentLang) {
			if ($contentLang->getId() !== 0) {
				$contentLangs[] = $contentLang;
			}
		}
		return $contentLangs;
	}

	private function saveContent() {
		foreach ($this->_contentLangs as $contentLang) {
			$contentLang->setIdContent( $this->getId() );
			$contentLang->save();
		}
	}
	
	public function save() {
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract */
		$change = false;
		if ($this->getId() === 0) {
			$db->insert( 'contents', array(
				'idDefaultLang' => $this->getIdDefaultLang(),
				'contentStatus' => self::STATUS_SHOW
			) );
			$this->_idContent = (int)$db->lastInsertId();
			$this->_changes = array();
			$change = true;
		} else {
			if (!empty($this->_changes)) {
				$db->update('contents', $this->_changes, 'idContent = ' . $this->getId());
				$this->_changes = array();
				$change = true;
			}
		}
		$this->saveContent();
		if ($change) {
			$this->clear();
			$this->cache();
		}
		return $this;
	}
	
	private static function loadFromCache($key) {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache( self::CACHE_NAME );
		return (($field = $cache->load($key)) !== false) ? $field : false;
	}
	
	private function cache() {
		$this->_contentLangs = array();
		$this->_loadedContentLangs = false;
		$cache = Zend_Registry::get('cachemanager')->getCache( self::CACHE_NAME );
		$cache->save( $this, $this->getId() );
	}
	
	private function clear() {
		$cache = Zend_Registry::get('cachemanager')->getCache( self::CACHE_NAME );
		$cache->remove( $this->getId() );
	}
	
	public function removeUnsetedLangs() {
		foreach ($this->getAllContentLangs() as $lang) {
			if ( !in_array($lang->getIdLang(), $this->_settedLangs) ) {
				$this->removeContentLang(
					RM_Lang::getById( $lang->getIdLang() )
				);
			}
		}
	}

	public function remove() {
		$db = Zend_Registry::get('db');
		$db->update('contents', array(
			'contentStatus' => self::STATUS_DROP
		), 'idContent = ' . $this->getId());
		foreach ($this->getAllContentLangs() as $contentLang) {
			$contentLang->remove();
		}
		$this->clear();
	}
	
}