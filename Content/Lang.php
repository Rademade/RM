<?php
class RM_Content_Lang {

	private $idContentLang;
	private $idContent;
	private $idLang;

	private $changes = array();
	private $loaded = false;
	
	/**
	 * @var Application_Model_System_Content_Field[]
	 */
	private $fields = array();
	
	const STATUS_SHOW = 1;
	const STATUS_DROP = 2;
	
	const CACHE_NAME = 'contentLang';
	const CACHE_LIST_NAME = 'contentLangList';

	public function __construct(
		$idContentLang,
		$idContent,
		$idLang
	) {
		$this->idContentLang = (int)$idContentLang;
		$this->idContent = (int)$idContent;
		$this->idLang = (int)$idLang;
	}
	
	private function loadFields() {
		if (!$this->loaded) {
			$this->loaded = true;
			$this->fields = array();
			$fields = RM_Content_Field::getList($this->getIdContent(), $this->getIdLang());
			foreach ($fields as $field) {
				/* @var $field RM_Content_Lang */
				$this->fields[ $field->getName() ] = $field;
			}
		}
	}
	
	private function clearLoadedFields() {
		$this->loaded = false;
		$this->fields = array();
	}

	public function getId() {
		return $this->idContentLang;
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

	private function checkField($name) {
		if ( !isset( $this->fields[ $name ] ) ) {
			//TODO if we create new field, always setups RM_Content_Field_Process_Text
			$this->fields[ $name ] = RM_Content_Field::getByName(
				$name,
				$this->getIdContent(),
				$this->getIdLang()
			);
		}
	}
	
	public function getAllFields() {
		$this->loadFields();
		return $this->fields;
	}
	
	public function setFieldContent($name, $value, $processType) {
		$name = mb_strtolower($name);
		$this->checkField($name);
		$field = $this->fields[ $name ];
		/*@var $field RM_Content_Field */
		$field->setProcessMethodType( $processType );
		$field->setContent($value);
		return $field;
	}
	
	/**
	 * @param RM_Content_Field $name
	 */
	public function getField($name) {
		$this->checkField($name);
		return $this->fields[ $name ];
	}

	public function getFieldContent($name) {
		$name = mb_strtolower($name);
		$this->checkField($name);
		return $this->fields[ $name ]->getContent();
	}
	
	public function removeField($name) {
		if (isset($this->fields[ $name ])) {
			$this->fields[ $name ]->remove();
			unset( $this->fields[ $name ] );	
		}
	}

	public function __call($name, $arguments) {
		if (preg_match('/^(set|get)([a-z]*)$/i', strtolower($name), $result)) {
			switch ($result[1]) {
				case 'set':
					$this->setFieldContent($result[2], $arguments[0], $arguments[1]);
					break;
				case 'get':
					return $this->getFieldContent($result[2]);
					break;
			}
		} else {
			throw new Exception('Unexpected function ' . $name);
		}
	}

	private function saveFields() {
		foreach ($this->fields as $field) {
			$field->setIdContent( $this->getIdContent() );
			$field->save();
		}
	}
	
	public function save() {
		$db = Zend_Registry::get('db');
		if ($this->getId() === 0) {
			$db->insert( 'contentLangs', array(
				'idContent' => $this->getIdContent(),
				'idLang' => $this->getIdLang(),
				'contentLangStatus' => self::STATUS_SHOW
			));
			$this->idContentLang = (int)$db->lastInsertId();
			$this->changes = array();
			$this->saveFields();
			$this->clear();
			$this->cache();
		} else {
			$this->saveFields();
			if (!empty($this->changes)) {
				$db->update( 'contentLangs', $this->changes, 'idContentLang = ' . $this->getId() );
				$this->changes = array();
				$this->clear();
				$this->cache();
			}
		}
	}

	public static function getByContent($idContent, $idLang) {
		$key = $idContent . '_' . $idLang;
		if ( ($contentLang = self::getFromCache( $key, self::CACHE_NAME ) ) === false) {
			$db = Zend_Registry::get('db');
			$select = $db->select()->from('contentLangs', array(
				'idContentLang'
			));
			$select->where('idContent = ?', $idContent);
			$select->where('idLang = ?', $idLang);
			$select->where('contentLangStatus = ?', self::STATUS_SHOW);
			$select->limit(1);
			if ( ($data = $db->fetchRow($select)) !== false) {
				$contentLang = new self($data->idContentLang, $idContent, $idLang);
				$contentLang->cache();
			} else {
				$contentLang = new self(0, $idContent, $idLang);
			}
		}
		$contentLang->loadFields();
		return $contentLang;
	}
	

	/**
	 * @static
	 * @param $idContent
	 * @return Application_Model_System_Content_Lang[]
	 */
	public static function getList($idContent) {
		if ( ($result = self::getFromCache( $idContent, self::CACHE_LIST_NAME ) ) === false) {
			$idContent = (int)$idContent;
			$db = Zend_Registry::get('db');
			$select = $db->select()->from('contentLangs', array(
				'idContentLang',
				'idLang'
			));
			$select->where('idContent = ?', $idContent);
			$select->where('contentLangStatus = ?', self::STATUS_SHOW);
			$result = array();
			if ( ($data = $db->fetchAll($select)) !== false) {
				foreach ($data as $row) {
					$result[] = new self($row->idContentLang, $idContent, $row->idLang);
				}
			}
			$cache = Zend_Registry::get('cachemanager')->getCache( self::CACHE_LIST_NAME );
			$cache->save( $result, $idContent );	
		}
		foreach ($result as &$contentLang) {
			$contentLang->loadFields();
		}
		return $result;
	}
	
	public function clear() {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache(self::CACHE_NAME);
		$cache->remove( $this->getIdContent() . '_' . $this->getIdLang() );
		$cache = $cachemanager->getCache(self::CACHE_LIST_NAME);
		$cache->remove( $this->getIdContent() );
	}
	
	private static function getFromCache($key, $cacheName) {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache($cacheName);
		return (($field = $cache->load($key)) !== false) ? $field : false;
	}
	
	public function remove() {
		$db = Zend_Registry::get('db');
		$db->update('contentLangs', array(
			'contentLangStatus' => self::STATUS_DROP
		), 'idContentLang = ' . $this->getId());
		foreach ($this->fields as $field) {
			$field->remove();
		}
		$this->clear();
	}

	public function cache() {
		$this->clearLoadedFields();//clear fields
		$cache = Zend_Registry::get('cachemanager')->getCache( self::CACHE_NAME );
		$cache->save( $this, $this->getIdContent() . '_' . $this->getIdLang() );
	}

}