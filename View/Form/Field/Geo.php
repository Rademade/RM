<?php
class RM_View_Form_Field_Geo
	extends RM_View_Form_Field {

	const TPL = 'geo.phtml';

	public function __construct($desc, $name, $value) {
		parent::__construct($name, $desc, $value);
		Head::getInstance()->getJS()->add('geo')->add('map');
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