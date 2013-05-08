<?php
abstract class RM_View_Form_Field {
		
	private $name;
	private $desc;

	private $value;
	private $view;
	
	private $disabled = false;
	private $hide = false;

	const BASE_PATH = 'blocks/form/fields/';
	
	public function __construct(
		$name,
		$desc,
		$value
	) {
		$this->setName($name);
		$this->setDesc($desc);
		$this->value = new RM_View_Form_Field_Value($value);
		$this->view = Zend_Layout::getMvcInstance()->getView();
	}

	public function getName() {
		return $this->name;
	}

	protected function getView() {
		return $this->view;
	}
	
	public function setName($name) {
		$this->name = mb_strtolower($name, 'utf-8');
	}
	
	public function getDesc() {
		return $this->desc;
	}
	
	public function setDesc($desc) {
		$this->desc = $desc;
	}
	
	public function getValue() {
		return $this->value;
	}

	public function getLangs() {
		return $this->value->getLangs();
	}
	
	public function isMultiLang() {
		return $this->value->isMultiLang();
	}

    /**
     * @return RM_View_Form_Field
     */
    public function disable() {
		$this->disabled = true;
		return $this;
	}
	
	public function isHide() {
		return $this->hide;
	}

    /**
     * @return RM_View_Form_Field
     */
    public function hide() {
		$this->hide = true;
		return $this;
	}
	
	protected function renderRow(RM_View_Form_Row $row) {
		if ($this->isHide()) {
			$row->hide();
		}
		return $row->render();
	}
	
	protected function addFieldData($idLang, $data) {
		return array_merge($data, array(
			'idLang' => $idLang,
			'disabled' => $this->disabled,
			'hide' => $this->hide,
			'name' => $this->getName(),
			'value' => $this->getValue()->getContent( $idLang )
		));
	}
	
	abstract public function render($idLang);
	
}