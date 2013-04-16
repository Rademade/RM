<?php
class RM_View_Form_Field_MultiList
    extends
        RM_View_Form_Field {

    private $_data = array();
    private $_options = array();

    const TPL = 'multi-list.phtml';

    public function __construct($desc, $name, $data, $options = array()) {
        parent::__construct($name, $desc, '');
        $this->_data = $data;
        $this->_options = $options;
        Head::getInstance()->getJS()->add('multi-list');
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array(
                'value' => array(),
                'options' => $this->_options
            ) )
        ));
        return $this->renderRow($row);
    }

}