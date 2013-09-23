<?php
use RM_View_ExtForm_Button as Button;

class RM_View_ExtForm
    extends
        RM_View_Form {

    const BUTTON_TPL = 'blocks/form/buttons.phtml';
    const FORM_NAME = 'mainForm';

    /**
     * @var Button[]
     */
    private $_buttons = array();

    public function __construct() {
        parent::__construct();
        $this->_initDefaultButtons();
    }

    public function clearButtons() {
        $this->_buttons = [];
        return $this;
    }
    
    public function addButton(Button $button) {
        $this->_buttons[] = $button;
        return $this;
    }

    public function renderButtons() {
        $row = new RM_View_Form_Row();
        $row->setHTML($this->_view->partial(self::BUTTON_TPL, array(
            'buttons' => array_reverse($this->_buttons)
        )));
        return $row->render();
    }

    private function _initDefaultButtons() {
        if (sizeof(RM_View_Top::getInstance()->getBreadcrumbs()) > 1) {
            $this->addButton( new Button('Cancel', 'cancel', 'sec-links', RM_View_Top::getInstance()->getBreadcrumbs()->getBack()) );
        }
        $this->addButton(new Button('Save', 'save'));
    }

}