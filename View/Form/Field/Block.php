<?php
class RM_View_Form_Field_Block
	extends RM_View_Form_Field {
	
	const TPL = 'block.phtml';
		
	public function __construct($desc, $value) {
		parent::__construct('', $desc, $value);
	}

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData($idLang, array())
		));
		return $this->renderRow( $row );
	}
	
}