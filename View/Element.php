<?php
abstract class RM_View_Element {

	private $_routeName;
	private $_routeData;

    private $_elementId;

	public function __construct(
		$routeName,
		array $routeData,
        $elementId = null
	) {
		$this->_routeName = $routeName;
		$this->setRouteDate($routeData);
        $this->_elementId = $elementId;
	}

	public function setRouteDate(array $params) {
		$this->_routeData = array_merge(
			Zend_Controller_Front::getInstance()->getRequest()->getParams(),
			$params
		);
	}

	public function getRouteName() {
		return $this->_routeName;
	}

	public function getRouteData() {
		return $this->_routeData;
	}

	public function getUrl() {
		return Zend_Layout::getMvcInstance()->getView()->url(
			$this->getRouteData(),
			$this->getRouteName()
		);
	}

    public function getId() {
        return $this->_elementId;
    }

}
