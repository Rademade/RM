<?php
class RM_View_Form_Field_Link
	extends RM_View_Form_Field {
	
	private $_href = '';
	
	const TPL = 'link.phtml';
		
	public function __construct($desc, $name, $href) {
		parent::__construct($name, $desc, '');
		$this->_href = $href;
	}
	
	public function getHref() {
		return $this->_href;
	}

	public function setHref($href) {
		$this->_href = $href;
		return $this;
	}

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData($idLang, array(
				'href' => $this->getHref()
			) )
		));
		return $this->renderRow( $row );
	}
	
}