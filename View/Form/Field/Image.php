<?php
class RM_View_Form_Field_Image
	extends RM_View_Form_Field {
	
	private $width = 0;
	private $height = 0;
	
	const TPL = 'image.phtml';
		
	public function __construct($desc, $name, $idPage, $width, $height) {
		parent::__construct($name, $desc, $idPage);
		Head::getInstance()->getJS()->add('upload');
		$this->setWidth($width);
		$this->setHeight($height);
	}

	public function setHeight($height) {
		$this->height = $height;
	}
	
	public function setWidth($width) {
		$this->width = $width;
	}

	public function getWidth() {
		return $this->width;
	}
	
	public function getHeight() {
		return $this->height;
	}
	
	private function getSizeObject() {
		$size = new stdClass();
		$size->width = $this->getWidth();
		$size->height = $this->getHeight();
		return $size;
	}
	
	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData($idLang, array(
				'size' => $this->getSizeObject(),
			) )
		));
		return $this->renderRow($row);
	}
	
}