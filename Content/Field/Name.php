<?php
/**
 * @property int idFieldName
 * @property mixed fieldName
 */
class RM_Content_Field_Name
	extends
		RM_Entity {

	const TABLE_NAME = 'fieldsNames';

	protected static $_properties = array(
		'idFieldName' => array(
			'id' => true,
			'type' => 'int'
		),
		'fieldName' => array(
			'type' => 'string'
		)
	);
	
	private static function create($name) {
		$fieldName = new self();
		$fieldName->setName($name);
		$fieldName->save();
		return $fieldName;
	}
	
	public function getId() {
		return $this->idFieldName;
	}
	
	public function getName() {
		return $this->fieldName;
	}
	
	public function setName($fieldName) {
		if (!$fieldName)
			throw new Exception('Empty field name given');
		if ($this->fieldName !== $fieldName) {
			if (self::getByName($fieldName, false))
				throw new Exception('Such name already exist');
			$fieldName = mb_strtolower( trim($fieldName) );
			$this->fieldName = $fieldName;
		}
	}

	public static function getByName($name, $create = true) {
		$select = self::_getSelect();
		$select->where('fieldName = ?', $name);
		$filedName = self::_initItem($select);
		if (!$filedName && $create)
			$filedName = self::create($name);
		return $filedName;
	}

}