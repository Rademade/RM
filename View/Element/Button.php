<?php
class RM_View_Element_Button
	extends RM_View_Element {

	private $_buttonName;

	public function __construct(
		$routeName,
		$routeData,
		$buttonName
	) {
		$this->_buttonName = $buttonName;
		parent::__construct(
			$routeName,
			$routeData
		);
	}

	public function getName() {
		return $this->_buttonName;
	}

}
