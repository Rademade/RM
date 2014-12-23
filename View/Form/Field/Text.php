<?php
class RM_View_Form_Field_Text
    extends
        RM_View_Form_Field {

    private $type = 'text';

    const TPL = 'text.phtml';

    public function __construct($desc, $name, $value = null) {
        parent::__construct($name, $desc, $value);
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc($this->getDesc());
        $row->setHTML($this->getView()->partial(
            static::BASE_PATH . static::TPL,
            $this->__getPartialData($idLang)
        ));
        return $this->renderRow($row);
    }

    protected function __getPartialData($idLang) {
        return $this->addFieldData($idLang, array(
            'type' => $this->getType()
        ));
    }

}