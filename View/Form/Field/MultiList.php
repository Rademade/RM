<?php
class RM_View_Form_Field_MultiList
    extends
        RM_View_Form_Field {

    private $_data = array();

    const TPL = 'multi-list.phtml';

    public function __construct($desc, $name, $data) {
        parent::__construct($name, $desc, '');
        $this->_data = $data;
        Head::getInstance()->getJS()->add('multi-list');
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array(
                'value' => array()
            ) )
        ));
        return $this->renderRow($row);
    }

}