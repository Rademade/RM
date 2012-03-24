<?php
/**
* @property int idField
* @property int idContent
* @property int idLang
* @property int idFieldName
* @property string fieldContent
* @property int processType
* @property int fieldStatus
*/
class RM_Content_Field
	extends
		RM_Entity
	implements
		RM_Interface_Deletable {

	const CACHE_NAME = 'fields';

	const TABLE_NAME = 'fieldsContent';

	protected static $_properties = array(
		'idField' => array(
			'id' => true,
			'type' => 'int'
		),
		'idContent' => array(
			'type' => 'int'
		),
		'idLang' => array(
			'type' => 'int'
		),
		'idFieldName' => array(
			'type' => 'int'
		),
		'processType' => array(
			'default' => RM_Content_Field_Process::PROCESS_TYPE_LINE,
			'type' => 'int'
		),
		'fieldContent' => array(
			'type' => 'string'
		),
		'fieldStatus' => array(
			'default' => self::STATUS_UNDELETED,
 			'type' => 'int'
		)
	);

	/**
	 * @var RM_Content_Field_Process
	 */
	private $_process;

	/**
	 * @var RM_Content_Field_Name
	 */
	private $_fieldName;

	public function  __construct(stdClass $data) {
		parent::__construct($data);
		$this->_fieldName = RM_Content_Field_Name::getById( $data->idFieldName );
	}

	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('fieldStatus != ?', self::STATUS_DELETED);
	}

	public function getIdName() {
		return $this->idFieldName;
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

	/**
	 * @name getFiledName
	 * @return RM_Content_Field_Name
	 */
	public function getFiledName() {
		return $this->_fieldName;
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
			$this->_process = null;
			$this->processType = $type;
		}
	}

	public function getProcessMethodType() {
		return $this->processType;
	}

	public function getProcessMethod() {
		if (!($this->_process instanceof RM_Content_Field_Process)) {
			$this->_process = RM_Content_Field_Process::getByType( $this->getProcessMethodType() );
		}
		return $this->_process;
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
			$this->fieldContent = $content;
		}
	}

	public function getContentLang() {
		return RM_Content_Lang::getByContent(
			$this->getIdContent(),
			$this->getIdLang()
		);
	}

	public function isEmptyContent() {
		return ($this->getContent() === '' || is_null($this->getContent()));//TODO all empty types
	}

	public function __refreshCache() {
		foreach (RM_Content_Lang::getByContent(
			$this->getIdContent(),
	        $this->getIdLang()
		) as $contentLang) {
			/* @var $contentLang RM_Content_Lang */
			$contentLang->__refreshCache();
		}
	}

	protected function __cache() {
		parent::__cache();
		$this->__cacheEntity( join('_', array(
            $this->getIdName(),
            $this->getIdContent(),
            $this->getIdLang()
        )));
	}

	public static function getByName($name, $idContent, $idLang) {
		$idFieldName = RM_Content_Field_Name::getByName( $name )->getId();
		$key = join('_', array(
			$idFieldName,
		    $idContent,
		    $idLang
        ));
		if (is_null($field = self::_getStorage()->getData($key))) {
			if (is_null($field = self::__load($key))) {
				$select = self::_getSelect();
				$select->where('idContent = ?', $idContent);
				$select->where('idLang = ?', $idLang);
				$select->where('idFieldName = ?', $idFieldName);
				$field = self::_initItem($select );
				if (is_null($field)) {
					$field =  new self( new RM_Compositor( array(
		                'idContent' =>$idContent,
		                'idLang' => $idLang,
					    'idFieldName' => $idFieldName
		            ) ) );
				}
				$field->__cache();
			}
			self::_getStorage()->setData($field, $key);
		}
		return $field;
	}

	public function remove() {
		$this->fieldStatus = self::STATUS_DELETED;
		$this->save();
		$this->__cleanCache();
	}

}
