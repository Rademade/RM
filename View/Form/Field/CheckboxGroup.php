<?php
class RM_View_Form_Field_CheckboxGroup
    extends
        RM_View_Form_Field {

    const TPL = 'checkbox-group.phtml';

    private $_data = array();

    public function __construct($desc, $name, $data, $value) {
        parent::__construct($name, $desc, '');
        $this->_data = $data;
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc($this->getDesc());
        $row->setHTML($this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array(
                'data' => $this->_data
            ))
        ));
        return $this->renderRow($row);
    }

}