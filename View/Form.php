<?php
class RM_View_Form  {

    /**
     * @var Zend_View
     */
    protected $_view;
    /**
     * @var RM_View_Form_Langs
     */
    private $_langPanel;
    /**
     * @var array of RM_View_Form_Field
     */
    private $_fields = array();

    private $_transationRenderIndex = 0;

    const BUTTON_TPL =  'blocks/form/button.phtml';
    const TABLE_TPL = 'blocks/form/backbone.phtml';
    const LANG_TABLE_TPL = 'blocks/form/langTable.phtml';

    const TMP_ID = '~tmp';

    public function __construct() {
        $this->_view = Zend_Layout::getMvcInstance()->getView();
        return $this;
    }

    /**
     * @return RM_View_Form_Langs
     */
    public function getLangPanel() {
        if (!$this->_langPanel instanceof RM_View_Form_Langs) {
            $this->_langPanel = new RM_View_Form_Langs();
        }
        return $this->_langPanel;
    }

    public function setLangPanel($panel) {
        $this->_langPanel = $panel;
    }

    public function add(RM_View_Form_Field $field) {
        $this->_fields[] = $field;
        if (!$field->isMultiLang()) {
            $this->getLangPanel()->setMultiLang();
            foreach ($field->getLangs() as $lang) {
                $this->getLangPanel()->addLang($lang);
            }
        }
        return $this;
    }

    public function addFields(array $fields) {
        foreach ($fields as $field) {
            $this->add($field);
        }
    }

    private function _renderFields( $idLang ) {
        $buffer = '';
        foreach ($this->_fields as $field) {
            /* @var $field RM_View_Form_Field */
            if (is_null($idLang)) {
                if ($field->isMultiLang()) {
                    $buffer .= $field->render( $idLang );
                }
            } else {
                if (!$field->isMultiLang()) {
                    $buffer .= $field->render( $idLang );
                }
            }
        }
        return $buffer;
    }

    public function renderSimpleFields() {
        return $this->_renderFields( null );
    }

    public function renderLangFields($idLang) {
        RM_View_Form_Row::startNewTranslation();
        return $this->_renderFields( $idLang );
    }

    /**
     * @param RM_Lang $lang
     * @param string $renderedFieldsHTML
     * @return string
     */
    protected function renderLangTable(
        RM_Lang $lang,
        $renderedFieldsHTML
    ) {
        ++$this->_transationRenderIndex;
        return $this->_view->partial(self::LANG_TABLE_TPL, array(
            'idLang' => $lang->getId(),
            'html' => $renderedFieldsHTML,
            'first' => $this->_transationRenderIndex === 1,
            'lang' => $lang
        ));
    }

    public function renderButtons() {
        $row = new RM_View_Form_Row();
        $row->setHTML( $this->_view->partial( self::BUTTON_TPL, array() ) );
        return $row->render();
    }

    public function renderTranslation() {
        $translateFields = '';
        if ($this->getLangPanel()->isMultiLang()) {
            $translateFields .= $this->getLangPanel()->renderLangsPanel();
            foreach ($this->getLangPanel()->getAllLangs() as $lang) {
                $translateFields .= $this->renderLangTable(
                    $lang,
                    $this->renderLangFields( $lang->getId() )
                );
            }
        }
        return $translateFields;
    }

    public function getEmptyLangTemplate() {
        if ($this->getLangPanel()->isResolveAddTabs()) {
            return $this->renderLangFields( self::TMP_ID );
        }
        return '';
    }

    /**
     * @return string
     */
    public function render() {
        return $this->_view->partial( self::TABLE_TPL, array(
            'form' => $this
        ));
    }

    public function __toString() {
        return $this->render();
    }

}