<?php
/**
* @property mixed idLog
* @property mixed logName
*/
class RM_Error_Category
	extends
		RM_Entity {

	const TABLE_NAME = '_errorLog';

	protected static $_properties = array(
		'idLog' => array(
			'id' => true,
			'type' => 'int'
		),
		'logName' => array(
			'type' => 'string'
		)
	);

	private $_rowCount;
	private $_newRowCount;

	public static function create($name) {
		$errorCategory = new self();
		$errorCategory->logName = $name;
		$errorCategory->save();
		return $errorCategory;
	}

	public static function init(stdClass $data) {
		return new self(
			$data->idLog,
			$data->logName
		);
	}
	
	public function getName() {
		return $this->logName;
	}

	public static function getErrorsCount(
		RM_Query_Where $conditions
	) {
		$select = self::getDb()->select()->from('_errorLogRow', array(
			'count'=> 'COUNT(idLogRow)'
		));
		$conditions->improveQuery($select);
		return (int)self::getDb()->fetchRow($select)->count;
	}

	public function getErrorCount() {
		if (!is_int($this->_rowCount)) {
			$conditions = new RM_Query_Where();
			$conditions->add('idLog', RM_Query_Where::EXACTLY, $this->getId());
			$this->_rowCount = RM_Error::getCount($conditions);
		}
		return $this->_rowCount;
	}

	public function getNewErrorCount() {
		if (!is_int($this->_newRowCount)) {
			$conditions = new RM_Query_Where();
			$conditions->add('idLog', RM_Query_Where::EXACTLY, $this->getId());
			$conditions->add('errorStatus', RM_Query_Where::EXACTLY, RM_Error::STATUS_NEW);
			$this->_newRowCount = RM_Error::getCount($conditions);
		}
		return $this->_newRowCount;
	}

	public static function getByLog($name) {
		$select = self::_getSelect();
		$select->where('logName = ?', $name);
		$category = self::_initItem($select);
		if (is_null($category)) {
			$category = self::create($name);
		}
		return $category;
	}
	
}