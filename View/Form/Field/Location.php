<?php
class RM_View_Form_Field_Location
    extends RM_View_Form_Field {

    private $lat;
    private $lng;
    private $width;
    private $height;
    private $zoom;

    const TPL = 'location.phtml';

    public function __construct(
        $desc,
        $name,
        $lat,
        $lng,
        $zoom,
        $width,
        $height
    ) {
        parent::__construct($name, $desc, '');
        Head::getInstance()->getJS()->add('map');
        $this->lat = $lat;
        $this->lng = $lng;
        $this->zoom = (int)$zoom;
        $this->width = (int)$width;
        $this->height = (int)$height;
    }

    public function render($idLang) {
        $row = new RM_View_Form_Row();
        $row->setDesc( $this->getDesc() );
        $row->setHTML( $this->getView()->partial(
            self::BASE_PATH . self::TPL,
            $this->addFieldData($idLang, array(
                'lat' => $this->lat,
                'lng' => $this->lng,
                'zoom' => $this->zoom,
                'width' => $this->width,
                'height' => $this->height
            ) )
        ));
        return $this->renderRow($row);
    }

}