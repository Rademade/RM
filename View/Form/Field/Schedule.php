<?php
class RM_View_Form_Field_Schedule
    extends RM_View_Form_Field {

    private $_options = array();
    private $_selected = array();

    const TPL = 'schedule.phtml';

    public function __construct(
        $desc,
        $name
    ) {
        parent::__construct($name, $desc, '');
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array(
                'options' => $this->_options,
                'selected' => $this->_selected
            ))
        ) );
        return $this->renderRow($row);
    }

}