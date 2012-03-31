<?php
/**
* @property int idContentLang
* @property int idContent
* @property int idLang
* @property int contentLangStatus
*/
class RM_Content_Lang
	extends
		RM_Entity
	implements
		RM_Interface_Deletable {

	private $loaded = false;

	const CACHE_NAME = 'fields';

	const TABLE_NAME = 'contentLangs';

	protected static $_properties = array(
		'idContentLang' => array(
			'id' => true,
			'type' => 'int'
		),
		'idContent' => array(
			'type' => 'int'
		),
		'idLang' => array(
			'type' => 'int'
		),
		'contentLangStatus' => array(
			'default' => self::STATUS_UNDELETED,
			'type' => 'int'
		)
 	);
	
	/**
	 * @var RM_Content_Field[]
	 */
	private $fields = array();

	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('contentLangStatus != ?', self::STATUS_DELETED);
	}

	public function loadFields() {
		if (!$this->loaded) {
			$this->loaded = true;
			$this->fields = array();
			$where = new RM_Query_Where();
			$where->add(
				'idContent',
				RM_Query_Where::EXACTLY,
				$this->getIdContent()
			);
			$where->add(
				'idLang',
				RM_Query_Where::EXACTLY,
				$this->getIdLang()
			);
			$fields = RM_Content_Field::getList($where);
			foreach ($fields as $field) {
				/* @var $field RM_Content_Field */
				$this->fields[ $field->getName() ] = $field;
			}
		}
	}
	
	private function clearLoadedFields() {
		$this->loaded = false;
		$this->fields = array();
	}

	public function setIdContent($id) {
		$this->idContent = (int)$id;
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
		$name = mb_strtolower($name, 'utf-8');
		$this->checkField($name);
		$field = $this->fields[ $name ];
		/* @var $field RM_Content_Field */
		$field->setProcessMethodType( $processType );
		$field->setContent($value);
		return $field;
	}
	
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
		return null;
	}

	/**
	 * @return RM_Lang
	 */
	public function getLang() {
		return RM_Lang::getById( $this->getIdLang() );
	}

	public function getContentManager() {
		return RM_Content::getById( $this->getIdContent() );
	}

	private function _saveFields() {
		foreach ($this->fields as $field) {
			$field->setIdContent( $this->getIdContent() );
			$field->save();
		}
	}

	public function __refreshCache() {
		parent::__refreshCache();
		$this->getContentManager()->__refreshCache();
	}

	public function __cachePrepare() {
		$this->loadFields();
	}

	protected function __cache() {
		parent::__cache();
		$this->__cacheEntity( $this->getIdContent()  . '_' . $this->getIdLang() );
	}

	/**
	 * @static
	 * @param $idContent
	 * @param $idLang
	 * @return RM_Content_Lang
	 */
	public static function getByContent($idContent, $idLang) {
		$key = $idContent . '_' . $idLang;
		if (is_null($contentLang = self::_getStorage()->getData($key))) {
			if (is_null($contentLang = self::__load($key))) {
				$select = self::_getSelect();
				$select->where('idContent = ?', $idContent);
				$select->where('idLang = ?', $idLang);
				$contentLang = self::_initItem($select );
				if (!($contentLang instanceof self)) {
					$contentLang =  new self( new RM_Compositor( array(
		                'idContent' => $idContent,
		                'idLang' => $idLang,
		            ) ) );
				}
				$contentLang->__cache();
			}
			self::_getStorage()->setData($contentLang, $key);
		}
		return $contentLang;
	}

	public function save() {
		parent::save();
		$this->_saveFields();
	}

	public function remove() {
		$this->contentLangStatus = self::STATUS_DELETED;
		$this->save();
		$this->__cleanCache();
	}

}
