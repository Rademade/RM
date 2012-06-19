<?php
class RM_View_Form_Field_Geo
	extends RM_View_Form_Field {

	private $queryToUrl;
	
	const TPL = 'geo.phtml';

	public function __construct($desc, $name, $value, $queryToUrl) {
		parent::__construct($name, $desc, $value);
        Head::getInstance()->getJS()->add('map')->add('geo');
		$this->setQueryUrl($queryToUrl);
	}
	
	public function getQueryUrl() {
		return $this->queryToUrl;
	}
	
	public function setQueryUrl($url) {
		$this->queryToUrl = $url;
	}

	public function render($idLang) {
		$row = new RM_View_Form_Row();
		$row->setDesc( $this->getDesc() );
		$row->setHTML( $this->getView()->partial(
			self::BASE_PATH . self::TPL,
			$this->addFieldData($idLang, array(
				'url' => $this->getQueryUrl()
			) )
		));
		return $this->renderRow($row);
	}
	
}