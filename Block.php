<?php
class RM_Block
	implements
		RM_Interface_Hideable,
		RM_Interface_Deletable,
		RM_Interface_Contentable {
	
	private $_idBlock = 0;
	private $_idPage = 0;
	private $_idContent = 0;
	private $_blockType = 0;
	private $_searchType = 0;
	private $_blockStatus;

	const TYPE_SIMPLE_NAME = 1;
	const TYPE_MULTI_NAME = 2;
	
	const SEARCH_TYPE_OPTION = 1;
	const SEARCH_TYPE_BLOCK = 2;
	
	const CACHE_NAME = 'block';
	private static $instances = array();
	
	private $_changes = array();
	/**
	 * @var RM_Content
	 */
	private $_content = null;
	
	public function __construct(
		$idBlock,
		$idPage,
		$idContent,
		$blockType,
		$searchType,
		$blockStatus
	) {
		$this->_idBlock = (int)$idBlock;
		$this->_idPage = (int)$idPage;
		$this->_idContent = (int)$idContent;
		$this->_blockType = (int)$blockType;
		$this->_searchType = (int)$searchType;
		$this->_blockStatus = (int)$blockStatus;
	}
	
	public static function init($data) {
		return new self(
			$data->idBlock,
			$data->idPage,
			$data->idContent,
			$data->blockType,
			$data->searchType,
			$data->blockStatus
		);
	}
	
	public static function getDbAttributes() {
		return array(
			'idBlock', 'idPage', 'idContent', 'blockType', 'searchType', 'blockStatus'
		);
	}

	public static function create($blockType, $idPage, $searchType) {
		$block = new self(0, $idPage, 0, $blockType, $searchType, self::STATUS_SHOW);
		$block->setContentManager( RM_Content::create() );
		return $block;
	}

	public static function getById($id) {
		$id = (int)$id;
		if (($block = self::cacheLoad($id)) !== false) {
			return $block;
		} else {
			$id = (int)$id;
			$db = Zend_Registry::get('db');
			$select = $db->select()->from('blocks', self::getDbAttributes())->where('idBlock = ?', $id);
			$select->where('blocks.blockStatus != ?', self::STATUS_DELETED);
			$select->limit(1);
			$block = self::init( $db->fetchRow($select) );
			self::cacheSave($id, $block);
			return $block;
		}
	}
	
	public static function getList(
		$idPage,
		$searchType,
		RM_Query_Order $order,
		$showStatus,
		RM_Query_Limits $limits
	) {
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('blocks', self::getDbAttributes());
		$select->where('blocks.blockStatus != ?', self::STATUS_DELETED);
		if ($order)
			$order->improveQuery($select);
		if ($showStatus) {
			$select->where('blocks.blockStatus = ?', $showStatus);
		}
		$select->where('blocks.idPage = ?', (int)$idPage);
		$select->where('blocks.searchType = ?', (int)$searchType);
		$blocks = $limits->getResult($select);
		foreach ($blocks as &$block) {
			$block = self::init($block);
		}
		return $blocks;
	}
	
	public function validate() {
		if ($this->getName() === '') {
			throw new Exception('Enter block name');
		}
	}

	public function save() {
		$db = Zend_Registry::get('db');
		$this->getContentManager()->save();
		if ($this->getId() === 0) {
			$db->insert('blocks', array(
				'idPage' => $this->getIdPage(),
				'idContent' => $this->getContentManager()->getId(),
				'blockType' => $this->getType(),
				'searchType' => $this->getSearchType(),
				'blockStatus' => $this->getStatus()
			));
			$this->_idBlock = (int)$db->lastInsertId();
			$this->_changes = array();
			return true;
		} else {
			if (!empty($this->_changes)) {
				$db->update('blocks', $this->_changes, 'idBlock = ' . $this->getId());
				$this->_changes = array();
				$this->clear();
				return true;
			}
		}
		return false;
	}

	public function getId() {
		return $this->_idBlock;
	}
	
	public function getIdPage() {
		return $this->_idPage;
	}
	
	public function getType() {
		return $this->_blockType;
	}
	
	public function getSearchType() {
		return $this->_searchType;
	}

	public function getIdContent() {
		return $this->_idContent;
	}
	
	public function getName() {
		return $this->getContentManager()->getDefaultContentLang()->getName();
	}
	
	public function setName($name) {
		if ($this->getName() !== $name) {
			$name = trim($name);
			if ($name === '') {
				throw new Exception('Enter block name');
			}
			if ( strlen($name) > 200) {
				throw new Exception('Block is too long');
			}
			$field = $this->getContentManager()->getDefaultContentLang()->setName(
				$name,
				RM_Content_Field_Process::PROCESS_TYPE_TEXT
			);
		}
	}

	public function setContentManager(RM_Content $contentManager) {
		if ($this->getIdContent() !== $contentManager->getId()) {
			$this->_idContent = $contentManager->getId();
			$this->_changes['idContent'] = $contentManager->getId();
		}
		$this->_content = $contentManager;
	}
	
	public function getContentManager() {
		if (is_null($this->_content)) {
			$this->_content = RM_Content::getById( $this->getIdContent() );
		}
		return $this->_content;
	}
	
	public function getDefaultContent() {
		return $this->getContentManager()->getDefaultContentLang();
	}
	
	public function getContent() {
		return $this->getContentManager()->getCurrentContentLang();
	}
	
	public function isShow() {
		return $this->getStatus() === self::STATUS_SHOW;
	}

	public function getStatus() {
		return $this->_blockStatus;
	}
	
	public function setStatus($status) {
		$status = (int)$status;
		if ($this->getStatus() !== $status) {
			$this->_blockStatus = $status;
			$this->_changes['blockStatus'] = $this->getStatus();
		}
	}
	
	public static function getInstance($key) {
		if (isset(self::$instances[$key])) {
			return self::$instances[$key];
		} else {
			return false;
		}
	}
	
	public static function setInstance($key, $data) {
		self::$instances[$key] = $data;
	}

	private static function cacheLoad($key) {
		if (($field = self::getInstance($key)) !== false) {
			return $field;
		}
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache( self::CACHE_NAME );
		$field = (($field = $cache->load($key)) !== false) ? $field : false;
		self::setInstance($key, $field);
		return $field;
	}
	
	private function clear() {
		$cache = Zend_Registry::get('cachemanager')->getCache( self::CACHE_NAME );
		$cache->remove($this->getId());
	}

	private static function cacheSave($key, $data) {
		self::setInstance($key, $data);
		$cache = Zend_Registry::get('cachemanager')->getCache( self::CACHE_NAME );
		$cache->save( $data, $key );
	}

	public function show() {
		if ($this->getStatus() !== self::STATUS_SHOW) {
			$this->setStatus(self::STATUS_SHOW);
			$this->save();
		}
	}
	
	public function hide() {
		if ($this->getStatus() !== self::STATUS_HIDE) {
			$this->setStatus(self::STATUS_HIDE);
			$this->save();
		}
	}
	
	public function remove() {
		$this->setStatus(self::STATUS_DELETED);
		$this->save();
		$this->getContentManager()->remove();
	}

}
