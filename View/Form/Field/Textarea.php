<?php
class RM_View_Form_Field_Textarea
	extends RM_View_Form_Field {
	
	const TPL = 'textarea.phtml';

	private $_processType;

	public function __construct($desc, $name, $value, $processType = RM_Content_Field_Process::PROCESS_TYPE_TEXT) {
		parent::__construct($name, $desc, $value);
		$this->_processType = $processType;
	}

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData($idLang, ['processType' => $this->_processType] )
		));
		return $this->renderRow($row);
	}

}