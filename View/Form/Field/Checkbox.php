<?php
class RM_View_Form_Field_Checkbox
	extends RM_View_Form_Field {
	
	const TPL = 'checkbox.phtml';
		
	public function __construct($desc, $name, $value) {
		parent::__construct($name, $desc, $value);
	}

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData($idLang, array() )
		));
		return $this->renderRow($row);
	}

}