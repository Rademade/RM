<?php
class RM_View_Form_Field_Categories
    extends RM_View_Form_Field {

    const TPL = 'categories.phtml';

    private $_defaultValues;

    private $_options;

    public function __construct(
        $desc,
        $name,
        array $default,
        array $options = array()
    ) {
        $this->_options = $options;
        $this->_defaultValues = $default;
        parent::__construct($name, $desc, '');
    }

    public function getDefaultValues() {
        return $this->_defaultValues;
    }

    public function setDefaultValues(array $values) {
        $this->_defaultValues = $values;
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData(
                $idLang,
                array_merge(
                    $this->_options,
                    array(
                        'defaultValues' => $this->getDefaultValues()
                    )
                )
            )
        ));
        return $this->renderRow( $row );
    }

}