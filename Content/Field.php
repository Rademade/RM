<?php
class RM_Content_Field {
	
	private $idField;
	private $idContent;
	private $idLang;
	private $processType;
	private $fieldContent;

	/**
	 * @var RM_Content_Field_Process
	 */
	private $process;

	/**
	 * @var RM_Content_Field_Name
	 */
	private $fieldName;
	private $changes = array();
	
	const STATUS_SHOW = 1;
	const STATUS_DROP = 2;
	
	const CACHE_NAME = 'field';
	const CACHE_LIST_NAME = 'fieldList';
	
	public function  __construct(
		$idField,
		$idContent,
		$idLang,
		RM_Content_Field_Name $fieldName,
		$processType,
		$fieldContent
	) {
		$this->idField = (int)$idField;
		$this->idContent = (int)$idContent;
		$this->idLang = (int)$idLang;
		$this->fieldName = $fieldName;
		$this->processType = (int)$processType;
		$this->fieldContent = $fieldContent;
	}

	public static function init($data) {
		return new self(
			$data->idField,
			$data->idContent,
			$data->idLang,
			RM_Content_Field_Name::getById( $data->idFieldName ),
			$data->processType,
			$data->fieldContent
		);
	}

	public function getId() {
		return $this->idField;
	}
	
	public function setIdContent($id) {
		$id = (int)$id;
		if ($this->getIdContent() !== $id) {
			$this->idContent = $id;
			$this->changes['idContent'] = $id;
		}
	}
	
	public function getIdContent() {
		return $this->idContent;
	}
	
	public function getIdLang() {
		return $this->idLang;
	}
	
	/**
	 * @name getFiledName
	 * @return RM_Content_Field_Name
	 */
	public function getFiledName() {
		return $this->fieldName;
	}
		
	public function getName() {
		return $this->getFiledName()->getName();
	}
	
	public function getContent() {
		return $this->fieldContent;
	}
	
	public function setProcessMethodType($type) {
		$type = (int)$type;
		if ($this->getProcessMethodType() !== $type) {
			$this->process = null;
			$this->processType = $type;
			$this->changes['processType'] = $type;
		}
	}

	public function getProcessMethodType() {
		return $this->processType;
	}

	public function getProcessMethod() {
		if (!($this->process instanceof RM_Content_Field_Process)) {
			$this->process = RM_Content_Field_Process::getByType( $this->getProcessMethodType() );
		}
		return $this->process;
	}
	
	public function getInitialContent() {
		return $this->getProcessMethod()->getInitialContent(
			$this->getContent()
		);
	}

	/**
	 * When we save content, we must process it - to html preview data,
	 * and function getContent() return html preview data
	 * if we need update data, we must give to setContent() *unprocessed (initial)* html
	 * @param $content
	 */
	public function setContent($content) {
		if ($this->getInitialContent() !== $content) {
			$content = $this->getProcessMethod()->getParsedContent($content);
			$this->changes['fieldContent'] = $content;
			$this->fieldContent = $content;
		}
	}

	public function isEmptyContent() {
		return ($this->getContent() === '' || is_null($this->getContent()));//TODO all empty types
	}

	public static function getByName(
		$name,
		$idContent,
		$idLang
	) {
		$key = self::generateCacheKey($name, $idContent, $idLang);
		if ( ($field = self::getFromCache($key, self::CACHE_NAME) ) === false) {
			$db = Zend_Registry::get('db');
			$select = $db->select()->from( 'fieldsContent', array(
				'idField',
				'processType',
				'fieldContent'
			));
			$select->where( 'idContent = ?', $idContent );
			$select->where( 'idLang = ?', $idLang );
			$fieldName = RM_Content_Field_Name::getByName($name);
			$select->where( 'idFieldName = ?', $fieldName->getId() );
			$select->where( 'fieldStatus = ?', self::STATUS_SHOW);
			$select->limit(1);
			if ( ($data = $db->fetchRow($select)) !== false ) {
				$field = new self(
					$data->idField,
					$idContent,
					$idLang,
					$fieldName,
					$data->processType,
					$data->fieldContent
				);
			} else {
				$field = new self(
					0,
					$idContent,
					$idLang,
					$fieldName,
					RM_Content_Field_Process::PROCESS_TYPE_TEXT, //TODO default process type, very hard
					''
				);
			}
			$field->cache();
		}
		return $field;
	}
	
	public static function getList($idContent, $idLang) {
		$db = Zend_Registry::get('db');
		$key = $idContent . '_' .  $idLang;
		if ( ($fields = self::getFromCache($key, self::CACHE_LIST_NAME) ) !== false) {
			return $fields;
		}
		$db = Zend_Registry::get('db');
		$select = $db->select()->from( 'fieldsContent', array(
			'idField',
			'idContent',
			'idLang',
			'idFieldName',
			'processType',
			'fieldContent'
		));
		$select->where( 'idContent = ?', $idContent );
		$select->where( 'idLang = ?', $idLang );
		$select->where( 'fieldStatus = ?', self::STATUS_SHOW);
		$result = array();
		if ( ($data = $db->fetchAll($select)) !== false ) {
			foreach ($data as $row) {
				$result[] = self::init($row);
			}
			$cachemanager = Zend_Registry::get('cachemanager');
			$cache = $cachemanager->getCache('fieldList');
			$cache->save($result, $key);
		}
		return $result;
	}
	
	public function save() {
		$db = Zend_Registry::get('db');
		if ($this->getId() === 0) {
			if (!$this->isEmptyContent()) {
				$db->insert( 'fieldsContent', array(
					'idContent' => $this->getIdContent(),
					'idLang' => $this->getIdLang(),
					'idFieldName' => $this->getFiledName()->getId(),
					'processType' => $this->getProcessMethodType(),
					'fieldContent' => $this->getContent(),
					'fieldStatus' => self::STATUS_SHOW
				) );
				$this->idField = (int)$db->lastInsertId();
				$this->changes = array();
				$this->clear();
				$this->cache();
				return true;
			}
		} else {
			if (!empty($this->changes)) {
				$db->update( 'fieldsContent', $this->changes, 'idField = ' . $this->getId() );
				$this->changes = array();
				$this->clear();
				$this->cache();
				return true;
			}
		}
		return false;
	}
	
	public function remove() {
		$db = Zend_Registry::get('db');
		$db->update('fieldsContent', array(
			'fieldStatus' => self::STATUS_DROP
		), 'idField = ' . $this->getId());
		$this->clear();
	}
	
	private static function generateCacheKey($name, $idContent, $idLang) {
		return md5($name) . '_' . $idContent . '_' . $idLang;
	}
	
	private function getCacheKey() {
		return self::generateCacheKey(
			$this->getName(),
			$this->getIdContent(),
			$this->getIdLang()
		);
	}
	
	public static function getFromCache($key, $cacheName) {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache($cacheName);
		return (($field = $cache->load($key)) !== false) ? $field : false;
	}
	
	public function clear() {
		$cachemanager = Zend_Registry::get('cachemanager');
		//one
		$cache = $cachemanager->getCache(self::CACHE_NAME);
		$cache->remove( $this->getCacheKey() );
		//in list		
		$cache = $cachemanager->getCache(self::CACHE_LIST_NAME);
		$cache->remove( $this->getIdContent() . '_' . $this->getIdLang() );
	}

	public function cache() {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache(self::CACHE_NAME);
		$cache->save($this, $this->getCacheKey());
	}

}
