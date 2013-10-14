<?php
class RM_View_Table {
	
	private $_records = array();
	private $_headData = array();
	private $_sortable = false;
	private $_firstPosition = 0;
	private $_editRouteName = null;
	private $_paginator = null;
    private $_paginatorOptions = false;
	private $_autoAdd = false;
	private $_statusUrl = '';
	private $_view;

	public function __construct() {
		$this->_view = Zend_Layout::getMvcInstance()->getView();
		Head::getInstance()->getJS()->add('list');
		return $this;
	}
	
	public function isEditble() {
		return is_string($this->_editRouteName);
	}
	
	public function setEditRoute($name) {
		$this->_editRouteName = $name;
		return $this;
	}
	
	public function getEditRoute() {
		return $this->_editRouteName;
	}

	public function setHead() {
		$this->_headData = func_get_args();
		return $this;
	}
	
	public function setSortable() {
		$this->_sortable = true;
		return $this;
	}
	
	public function hasAjaxElements() {
		return is_string($this->_statusUrl);
	}
	
	public function getMinPosition() {
		if (!$this->isSortable()) {
			return 0;
		}
		if (!empty($this->_records)) {
			$this->_firstPosition = $this->_records[0]->getPosition();
			foreach ($this->_records as $record) {
				/* @var $record RM_View_Table_Row */
				if ($this->_firstPosition > $record->getPosition()) {
					$this->_firstPosition = $record->getPosition();
				}
			}
		}
		return $this->_firstPosition;
	}
	
	public function isSortable() {
		return !is_null($this->_statusUrl) && $this->_sortable;
	}
	
	public function setStatusUrl($url) {
		$this->_statusUrl = $url;
		return $this;
	}

    /**
     * @param Zend_Paginator $paginator
     *
     * Available keys:
     *  - itemsOnPage
     *  - selectedOnPage
     * @param array          $paginatorOptions
     *
     * @return RM_View_Table
     */
    public function addPaginator(Zend_Paginator $paginator, array $paginatorOptions = array()) {
		$this->_paginator = $paginator;
        $this->_paginatorOptions = $paginatorOptions;
		return $this;
	}

	public function getStatusUrl() {
		return $this->_statusUrl;
	}

	public function isAutoAdd(){
		return $this->_autoAdd;
	}

	public function setAutoAdd() {
		$this->_autoAdd = true;
		return $this;
	}
	
	public function addRecord($id, $name) {
        if (strlen($name) == strlen(strip_tags($name))) {
            $name = $this->_view->CutText($name, 45);
        }
		$row = new RM_View_Table_Row($id, $name);
		if ( $this->isEditble() ):
			$row->setEditRouteName(
				$this->getEditRoute()
			);
		endif;
		$this->_records[] = $row;
		return $row;
	}
	
	public function renderThead() {
		return $this->_view->partial('blocks/table/head.phtml', array(
			'thead' => $this->_headData
		));
	}
	
	public function hasFooter() {
		return ($this->hasPaginator());
	}
	
	public function hasPaginator() {
		if ($this->_paginator instanceof Zend_Paginator) {
			return ($this->_paginator->getPages()->pageCount > 1);
		} else {
			return false;
		}
	}

	
	public function renderPaginator() {
		if (!is_null($this->_paginator)) {
			return $this->_view->paginationControl($this->_paginator, 'Sliding', 'blocks/table/paginator.phtml', $this->_paginatorOptions);
		} else {
			return false;
		}
	}
	
	public function renderTbody() {
		$HTML = '';
		foreach ($this->_records as $row) {
			$HTML .= $row->render();
		}
		return $HTML;
	}
	
	public function __toString() {
		try {
			return $this->_view->partial('/blocks/table/table.phtml', array(
				'table' => $this
			));
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

}