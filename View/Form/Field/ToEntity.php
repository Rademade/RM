<?php
class RM_View_Form_Field_ToEntity
    extends
        RM_View_Form_Field {

    const TPL = 'to-entity.phtml';

    private $_entityName;

    private $_searchUrl;

    public function __construct(
        $desc,
        $name,
        $entityName,
        $searchUrl,
        $value
    ) {
        $this->_entityName = $entityName;
        $this->_searchUrl = $searchUrl;
        Head::getInstance()->getJS()->add('item');
        parent::__construct($name, $desc, $value);
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData( $idLang, array(
                'entityName' => $this->_entityName,
                'searchUrl' => $this->_searchUrl
            ) )
        ));
        return $this->renderRow( $row );
    }

}