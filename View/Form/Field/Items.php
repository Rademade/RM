<?php
class RM_View_Form_Field_Items
	extends RM_View_Form_Field {

	const TPL = 'items.phtml';

	private $_idRoot;
	private $_defaultValues;

	public function __construct(
		$desc,
		$name,
		array $default
	) {
		$this->_defaultValues = $default;
		parent::__construct($name, $desc, '');
	}

	public function getDefaultValues() {
		return $this->_defaultValues;
	}

	public function setDefaultValues(array $values) {
		$this->_defaultValues = $values;
	}

	public function getIdRoot() {
		return $this->_idRoot;
	}

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData(
				$idLang,
				array(
				     'defaultValues' => $this->getDefaultValues()
				)
			)
		));
		return $this->renderRow( $row );
	}

}