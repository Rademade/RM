<?php
class RM_View_Table_Row {
	
	/**
	 * @var int
	 */
	private $_id;
	/**
	 * @var string
	 */
	private $_name;
	private $_url;
	private $_editRoute;
	private $_tds = array();
	private $_showUrl;
    private $_manualPosition;
	private $_isDeleteble = false;
	private $_isHideble = false;
	private $_isStockable = false;
	private $_withCheckBox = false;
	private $_stockStatus;
    private $_stockPrice;
	private $_currentStatus;
	private $_position;
	private $_index;
    private $_color;
	private $_icons = array();

	private static $_number = 0;

	public function __construct($id, $name) {
		$this->_id = (float)$id;
		$this->_name = $name;
	}

	public function getId() {
		return $this->_id;
	}
	
	public function getName() {
		return $this->_name;
	}

	public function isUrlSeted() {
		return is_string($this->_url);
	}
	
	private function _mergeParams(array $routeData) {
		return array_merge(
			Zend_Controller_Front::getInstance()->getRequest()->getParams(),
			$routeData
		);
	}

	public function setUrl($routeData, $routeName) {
		$this->_url = Zend_Layout::getMvcInstance()->getView()->url(
			$this->_mergeParams($routeData),
			$routeName
		);
		return $this;
	}
	
	public function getUrl() {
		return $this->_url;
	}
	
	public function isEditble() {
		return is_string($this->_editRoute);
	}

	public function setEditRouteName($routeName) {
		$this->_editRoute = $routeName;
		return $this;
	}
	
	public function getEditRouteName() {
		return $this->_editRoute;
	}
	
	public function getEditUrl() {
		return Zend_Layout::getMvcInstance()->getView()->url(
			$this->_mergeParams(array(
				'id' => $this->getId()
			)),
			$this->getEditRouteName()
		);
	}
	
	public function hasPreview() {
		return is_string($this->_showUrl);
	}
	
	public function setPreviewUrl($url) {
		$this->_showUrl = $url;
		return $this;
	}
	
	public function getPreviewUrl() {
		return $this->_showUrl;
	}
	
	public function addTd($str) {
		$this->_tds[] = $str;
		return $this;
	}
	
	public function getTds() {
		return $this->_tds;
	}

	public function setStatus($status) {
		$this->_currentStatus = (int)$status;
		return $this;
	}
	
	public function getStatus() {
		return $this->_currentStatus;
	}

	public function setHideble() {
		$this->_isHideble = true;
		return $this;
	}
	
	public function isHideble() {
		return $this->_isHideble;
	}

	public function setDeletable() {
		$this->_isDeleteble = true;
		return $this;
	}
	
	public function isDeletable() {
		return $this->_isDeleteble;
	}
	
	public function getPosition() {
		if (is_int($this->_position)) {
			return $this->_position;
		} else {
			throw new Exception('Position not setted');
		}
	}

	public function setPosition($position) {
		$this->_position  = (int)$position;
		return $this;
	}
	
	public function getIndex() {
		return $this->_index;
	}
	
	public function isGrey() {
		return $this->getIndex()%2;
	}

	public function setStockable($status, $price) {
		$this->_isStockable = true;
		$this->_stockStatus = (int)$status;
        $this->_stockPrice = $price;
		return $this;
	}

    public function isManualSortable() {
        return $this->_manualPosition instanceof stdClass;
    }

    public function getManualPositionMax() {
        return $this->_manualPosition->max;
    }

    public function getManualPositionMin() {
        return $this->_manualPosition->min;
    }

    public function getManualPosition() {
        return $this->_manualPosition->position;
    }

    public function addManualPosition($position, $min, $max) {
        $this->_manualPosition = new stdClass();
        $this->_manualPosition->position = $position;
        $this->_manualPosition->min = $min;
        $this->_manualPosition->max = $max;
        return $this;
    }

	public function getStockStatus() {
		return $this->_stockStatus;
	}

    public function getStockPrice() {
        return $this->_stockPrice;
    }

	public function isStockable() {
		return $this->_isStockable;
	}

	public function addCheckBox() {
		$this->_withCheckBox = true;
		return $this;
	}

	public function isWithCheckBox() {
		return $this->_withCheckBox;
	}

    public function setColor($color) {
        $this->_color = $color;
        return $this;
    }

    public function getColor() {
        return $this->_color;
    }

	public function addIcon(RM_View_Element_Icon $icon) {
		$this->_icons[] = $icon;
		return $this;
	}

	/**
	 * @return RM_View_Element_Icon[]
	 */
	public function getIcons() {
		return $this->_icons;
	}

	public function render() {
		++self::$_number;
		$this->_index = self::$_number;
		return Zend_Layout::getMvcInstance()->getView()->partial('/blocks/table/tr.phtml', array(
			'row' => $this
		));
	}
	
}