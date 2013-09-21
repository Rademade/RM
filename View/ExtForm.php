<?php
class RM_View_ExtForm
    extends
        RM_View_Form {

    const BUTTON_TPL = 'blocks/form/buttons.phtml';
    const FORM_NAME = 'mainForm';

    /**
     * @var RM_View_ExtForm_Button[]
     */
    private $_buttons = array();
    private $_cancelButton;

    public function __construct() {
        parent::__construct();
        $this->_initDefaultButtons();
    }

    public function addButton(RM_View_ExtForm_Button $button) {
        $this->_buttons[] = $button;
    }

    public function renderButtons() {
        $this->_buttons[] = $this->_cancelButton;
        $row = new RM_View_Form_Row();
        $row->setHTML($this->_view->partial(self::BUTTON_TPL, array(
            'buttons' => $this->_buttons
        )));
        return $row->render();
    }

    private function _initDefaultButtons() {
        $this->addButton(new RM_View_ExtForm_Button('Save', 'save'));
        if (sizeof(RM_View_Top::getInstance()->getBreadcrumbs()) > 1) {
            $this->_cancelButton = new RM_View_ExtForm_Button('Cancel', 'cancel', 'sec-links', RM_View_Top::getInstance()->getBreadcrumbs()->getBack());
        }
    }

}