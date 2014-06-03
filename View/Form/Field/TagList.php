<?php
class RM_View_Form_Field_TagList
    extends
        RM_View_Form_Field {

    const TPL = 'tags.phtml';

    public function __construct($desc, $name, $value = null) {
        parent::__construct($name, $desc, $value);
        RM_Head::getInstance()->getJS()->add('tags');
        RM_Head::getInstance()->getCSS()->add('tags');
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc($this->getDesc());
        $row->setHTML($this->getView()->partial(
            static::BASE_PATH . static::TPL,
            $this->addFieldData($idLang, array())
        ));
        return $this->renderRow($row);
    }

}