<?php
class RM_View_Form_Field_Select
	extends RM_View_Form_Field {
	
	private $data = array();
    private $_additionalClasses = array();

	const TPL = 'select.phtml';

	public function __construct($desc, $name, $data, $value = null) {
		parent::__construct($name, $desc, $value);
		$this->setData($data);
	}

	public function getData() {
		return $this->data;
	}
	
	public function setData($data) {
		$this->data = $data;
	}

    public function addClass($name) {
        $this->_additionalClasses[] = $name;
        return $this;
    }

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData($idLang, array(
				'data' => $this->getData(),
                'additionalClasses' => $this->_additionalClasses
			) )
		));
		return $this->renderRow($row);
	}

}