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
		RM_Interface_Deletable,
        JsonSerializable {

	const AUTO_CACHE = false;
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

    private $loaded = false;

    /**
	 * @var RM_Content_Field[]
	 */
	private $fields = array();

	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('contentLangStatus != ?', self::STATUS_DELETED);
	}

    public function destroy() {
        foreach ($this->fields as &$field) $field->destroy();
        $this->clearLoadedFields();
        parent::destroy();
    }

    /**
     * @return RM_Content_Lang
     */
    public function duplicate() {
        $data = $this->toArray();
        $data['idContentLang'] = 0;
        $self = new self( new RM_Compositor($data) );
        foreach ($this->fields as $field) {
            $self->fields[ $field->getName() ] = $field->duplicate();
        }
        $self->save();
        return $self;
    }

	public function loadFields() {
		if (!$this->loaded) {
			$this->loaded = true;
			$this->fields = array();
			$where = new RM_Query_Where();
			$where->add('idContent', '=', $this->getIdContent());
			$where->add('idLang', '=', $this->getIdLang());
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
	
	public function setFieldContent($name, $value, $processType = RM_Content_Field_Process::PROCESS_TYPE_LINE) {
		$name = mb_strtolower($name, 'utf-8');
		$this->checkField($name);
		$field = $this->fields[ $name ];
		/* @var $field RM_Content_Field */
		$field->setProcessMethodType( $processType );
		$field->setContent($value);
		return $field;
	}

    /**
     * @param $name
     * @return RM_Content_Field
     */
	public function getField($name) {
        $name = mb_strtolower($name, 'utf-8');
		$this->checkField($name);
		return $this->fields[ $name ];
	}

	public function getFieldContent($name) {
        $name = mb_strtolower($name, 'utf-8');
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
		if (preg_match('/^(set|get)([a-z]+)$/i', strtolower($name), $result)) {
			switch ($result[1]) {
				case 'set':
                    array_unshift($arguments, $result[2]);
                    call_user_func_array(array($this, 'setFieldContent'), $arguments);
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
		foreach ($this->fields as &$field) {
			$field->setIdContent( $this->getIdContent() );
			$field->save();
		}
	}

	public function __cachePrepare() {
		$this->loadFields();
	}

	/**
	 * @static
	 * @param $idContent
	 * @param $idLang
	 * @return RM_Content_Lang
	 */
	public static function getByContent($idContent, $idLang) {
		$select = self::_getSelect();
		$select->where('idContent = ?', $idContent);
		$select->where('idLang = ?', $idLang);
		$contentLang = self::_initItem($select );
		if (!($contentLang instanceof self)) {
			$contentLang = new self( new RM_Compositor( array(
                'idContent' => $idContent,
                'idLang' => $idLang,
            ) ) );
		}
		return $contentLang;
	}

    /**
     * @return RM_Content_Lang
     */
    public function save() {
    	parent::save();
		$this->_saveFields();
		$this->__refreshCache();
        $this->getContentManager()->__cleanCache();
        return $this;
	}

	public function remove() {
		$this->contentLangStatus = self::STATUS_DELETED;
		$this->save();
		$this->__cleanCache();
	}

    public function jsonSerialize() {
        $fields = array();
        foreach ($this->getAllFields() as $field) {
            $fields[ $field->getName() ] = $field->getContent();
        }
        return $fields;
    }

    public function toArray() {
        return array(
            'idContentLang' => $this->idContentLang,
            'idContent' => $this->idContent,
            'idLang' => $this->idLang,
            'contentLangStatus' => $this->contentLangStatus
        );
    }
}
