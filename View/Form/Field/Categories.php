<?php
class RM_View_Form_Field_Categories
	extends RM_View_Form_Field {

	const TPL = 'categories.phtml';

	private $_defaultValues;

	private $_idRootCategory;

	public function __construct(
		$desc,
		$name,
		$idRootCategory,
		array $default
	) {
		$this->_idRootCategory = $idRootCategory;
		$this->_defaultValues = $default;
		parent::__construct($name, $desc, '');
	}

	public function getDefaultValues() {
		return $this->_defaultValues;
	}

	public function getIdRootCategory() {
		return $this->_idRootCategory;
	}

	public function setDefaultValues(array $values) {
		$this->_defaultValues = $values;
	}

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData(
				$idLang,
				array(
				     'idRoot' => $this->getIdRootCategory(),
				     'defaultValues' => $this->getDefaultValues()
				)
			)
		));
		return $this->renderRow( $row );
	}

}