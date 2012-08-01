<?php
class RM_System_Breadcrumbs implements Iterator, Countable {

	protected $_breadcrumbs = array();
	/**
	 * @var Zend_View
	 */
	private $_view;

	public function __construct() {
		$this->_view = Zend_Layout::getMvcInstance()->getView();
	}

	/**
	 * @return Zend_View
	 */
	public function getView() {
		return $this->_view;
	}
	
	private function _compleateParams(array $params) {
		$params = array_merge(
			Zend_Controller_Front::getInstance()->getRequest()->getParams(),
			$params
		);
		if (!isset($params['page'])) {
			$params['page'] = 1;
		}
		return $params;
	}

    /**
     * @internal param string $name Breadcrumb name
     * @internal param array|string $url |$routeData Url or route data
     * @internal param null|string $routeName If second argument is array, third parameter must be route name
     * @return RM_System_Breadcrumbs
     */
    public function add() {
        $args = func_get_args();
        if (func_num_args() == 2 && gettype($args[1]) == 'string') {
            return $this->_addWithUrl($args[0], $args[1]);
        } else {
            return call_user_func_array(array($this, '_addWithRoute'), $args);
        }
    }

    private function _addWithUrl($name, $url) {
        array_push($this->_breadcrumbs, array(
            'name' => $name,
            'url' => $url
        ));

        return $this;
    }

    private function _addWithRoute($name, array $routeData, $routeName) {
        array_push($this->_breadcrumbs, array(
            'name' => $name,
            'url' => $this->getView()->url(
                $this->_compleateParams( $routeData ),
                $routeName
            )
        ));

        return $this;
    }


    public function getBack() {
		return $this->_breadcrumbs[sizeof($this->_breadcrumbs)-2]['url'];
	}
	
	public function addPageBack($idPage, array $params = array()) {
		$page = RM_Page::getById($idPage);
		$this->add(
			$page->getContent()->getName(),
			$this->_compleateParams( $params ),
			$this->getView()->GetListRouteName( $page )
		);
	}
	
	public function clear() {
		$this->_breadcrumbs = array();
		return $this;
	}

	public function rewind() {
		reset($this->_breadcrumbs);
    }

    public function current() {
		$current = current($this->_breadcrumbs);
		return $current['url'];
    }

    public function key() {
		$current = current($this->_breadcrumbs);
		return $current['name'];
    }

    public function next() {
		return next($this->_breadcrumbs);
    }

    public function valid() {
        $key = key($this->_breadcrumbs);
        return ($key !== null && $key !== false);
    }

    public function count() {
        return sizeof($this->_breadcrumbs);
    }
    
}