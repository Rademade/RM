<?php
class RM_View_Form_Field_Color
    extends RM_View_Form_Field {

    const TPL = 'color.phtml';

    public function __construct($desc, $name, $hexValue) {
        parent::__construct($name, $desc, $hexValue);
        Head::getInstance()->getJS()->add('color');
        Head::getInstance()->getCSS()->add('color');
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array() )
        ));
        return $this->renderRow($row);
    }

}