<?php
class RM_View_Form_Langs {
	
	private $_allLangs = array();
	/**
	 * @var RM_Lang
	 */
	private $_defaultLang = null;
	private $_langs = array();
	
	private $_multiLang = false;
	
	private $_resolveDeleteLangs = true;
	private $_resolveAddTabs = false;
	
	const TAB_TPL = 'blocks/form/tabs.phtml';
	
	public function __construct() {
		Head::getInstance()->getJS()->add('langs');
		$order = new RM_Query_Order();
		$order->addOrder('idLang', RM_Query_Order::DESC);
		$this->_allLangs = RM_Lang::getList(false, $order);
		$this->_defaultLang = RM_Lang::getDefault();
		$this->addLang($this->_defaultLang);
		$this->_collectLangs();
	}

	public function setMultiLang() {
		$this->_multiLang = true;
	}

	public function isMultiLang() {
		return $this->_multiLang;
	}

	public function addLang(RM_Lang $lang) {
		$this->_multiLangs = $lang;
		$this->_langs[ $lang->getId() ] = $lang;
	}
	
	public function getLangs() {
		return $this->_langs;
	}
	
	public function getAllLangsIds() {
		$ids = array();
		foreach ($this->getAllLangs() as $lang) {
			$ids[] = $lang->getId();
		}
		return $ids;
	}
	
	public function getLangIds() {//TODO save result in class
		$ids = array();
		foreach ($this->getLangs() as $lang) {
			$ids[] = $lang->getId();
		}
		return $ids;
	}

	public function getUnaddedLangsIds() {
		$ids = array();
		foreach ($this->getAllLangsIds() as $id) {
			if (!in_array($id, $this->getLangIds())) {
				$ids[] = $id;
			}
		}
		return $ids;
	}
	
	/**
	 * @return RM_Lang
	 */
	public function getDefaultLang() {
		return $this->_defaultLang;
	}
	
	public function allLangs() {
		$this->__resoleAddTabs = false;
		foreach ($this->_allLangs as $lang) {
			$this->_resolveDeleteLangs = false;
			$this->addLang($lang);
		}
	}
	
	public function getAllLangs() {
		return $this->_allLangs;
	}
	
	public function resolveAddTabs() {
		$this->_resolveDeleteLangs = true;
		$this->_resolveAddTabs = true;
		return $this;
	}
	
	public function isResolveAddTabs() {
		return $this->_resolveAddTabs;
	}
	
	public function isResolveDeleteTabs() {
		return $this->_resolveDeleteLangs;
	}
	
	public function allLangsAdded() {
		return sizeof($this->getAllLangs()) === sizeof($this->getLangs());
	}
	
	public function renderLangsPanel() {
		return Zend_Layout::getMvcInstance()->getView()->partial(self::TAB_TPL, array(
			'formLangs' => $this
		));
	}
	
	public function isDeleteble(RM_Lang $lang) {
		return $lang->getId() !== $this->getDefaultLang()->getId() &&
			$this->isResolveDeleteTabs();
	}

	private function _collectLangs() {
		if (isset($_POST['lang'])) {
			foreach ($_POST['lang'] as $idLang => $fields) {
                $lang = RM_Lang::getById( $idLang );
                if ($lang instanceof RM_Lang) {
                    $this->addLang( $lang );
                }
			}
		}
	}
	
}