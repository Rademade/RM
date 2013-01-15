<?php
class RM_View_Form_Field_MultiList
    extends
        RM_View_Form_Field {

    private $data = array();

    private $_value = array();

    const TPL = 'multi-list.phtml';

    public function __construct($desc, $name, $data, $value = array()) {
        parent::__construct($name, $desc, '');
        $this->_value = $value;
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array(
                //empty
            ) )
        ));
        return $this->renderRow($row);
    }

}