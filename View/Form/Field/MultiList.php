<?php
class RM_View_Form_Field_MultiList
    extends
        RM_View_Form_Field {

    private $_data = [];
    private $_options = [];
    private $_defaultKeys = [];
    private $_buttonsEnabled = true;

    const TPL = 'multi-list.phtml';

    public function __construct($desc, $name, $data, array $options = array(), array $defaultKeys = array('')) {
        parent::__construct($name, $desc, '');
        $this->_data = $data;
        $this->_options = $options;
        $this->_defaultKeys = $defaultKeys;
        Head::getInstance()->getJS()->add('multi-list');
    }

    public function disableButtons() {
        $this->_buttonsEnabled = false;
        return $this;
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array(
                'value' => array(),
                'defaultKeys' => $this->_defaultKeys,
                'options' => $this->_options,
                'buttonsEnabled' => $this->_buttonsEnabled
            ) )
        ));
        return $this->renderRow($row);
    }

}