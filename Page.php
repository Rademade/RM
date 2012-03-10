<?php
/**
 * @property int idPage
 * @property int idRoute
 * @property int idContent
 * @property int pageStatus
 * @property int pageType
 */
class RM_Page
	extends
		RM_Entity
	implements
		RM_Interface_Hideable,
		RM_Interface_Deletable,
		RM_Interface_Contentable {

	const TABLE_NAME = 'pages';

	protected static $_properties = array(
		'idPage' => array(
			'id' => true,
			'type' => 'int'
		),
		'idRoute' => array(
			'type' => 'int'
		),
		'idContent' => array(
			'type' => 'int'
		),
		'pageStatus' => array(
			'default' => self::STATUS_HIDE,
			'type' => 'int'
		),
		'pageType' => array(
			'type' => 'int'
		)
	);

	/**
	 * @var RM_Entity_Worker
	 */
	private $_entityWorker;

	/**
	 * @var RM_Content
	 */
	private $_content;
	/**
	 * @var RM_Routing
	 */
	private $_route;

	const TYPE_PAGE = 1;
	const TYPE_CATEGORY = 2;

	public function __construct($data) {
		if ($this->isEntity()) {
			$this->_entityWorker = new RM_Entity_Worker(
				'RM_Page',
				$data,
				RM_Page::TABLE_NAME
			);
		}
		parent::__construct($data);
	}

	private function isEntity() {
		return get_class() !== get_called_class();
	}

	public function __get($name) {
		$val = ($this->isEntity()) ? $this->_entityWorker->getValue($name) : null;
		return (is_null($val)) ? parent::__get($name) : $val;
	}

	public function __set($name, $value) {
		$parent = true;
		if ($this->isEntity()) {
			$parent = is_null($this->_entityWorker->setValue($name, $value));
		}
		if ($parent)
			parent::__set($name, $value);
	}

	protected function __setPageData( $controller, $action, $url) {
		$route = RM_Routing::create('~tmp', $controller, $action, $url);
		$this->setRoute( $route );
		$this->setContentManager( RM_Content::create() );
	}

	public function validate(RM_Exception $e = null, $throw = true) {
		if (is_null($e)) {
			$e = new RM_Exception();
		}
		$this->getRoute()->validate($e);
		if ($throw && (bool)$e->current()) {
			throw $e;
		}
	}
	
	public function saveRoteDate() {
		if (intval($this->getRoute()->idPage) !== $this->getId()) {
			$this->getRoute()->setName( $this->getId() );
			$this->getRoute()->idPage = $this->getId();
			$this->getRoute()->save();
		}
	}

	public function save() {
		$this->idContent = $this->getContentManager()->save()->getId();
		$this->idRoute = $this->getRoute()->save()->getId();
		if ($this->isEntity()) {
			$this->_entityWorker->save();
			parent::__set('idPage', $this->idPage);
		}
		parent::save();
		$this->saveRoteDate();
		return true;
	}
	
	public function getIdPage() {
		return $this->idPage;
	}
	
	public function getIdContent() {
		return $this->idContent;
	}
	
	public function setContentManager(RM_Content $contentManager) {
		if ($this->getIdContent() !== $contentManager->getId()) {
			$this->idContent = $contentManager->getId();
		}
		$this->_content = $contentManager;
	}
	
	public function getContentManager() {
		if (!($this->_content instanceof RM_Content)) {
			$this->_content = RM_Content::getById( $this->getIdContent() );
		}
		return $this->_content;
	}
	
	public function getDefaultContent() {
		return $this->getContentManager()->getDefaultContentLang();
	}
	
	public function getContent() {
		return $this->getContentManager()->getCurrentContentLang();
	}
	
	public function getIdRoute() {
		return $this->idRoute;
	}
	
	public function setRoute(RM_Routing $route) {
		if ($this->getIdRoute() !== $route->getId()) {
			$this->idRoute = $route->getId();
		}
		$this->_route = $route;
	}

	/**
	 * @return RM_Routing
	 */
	public function getRoute() {
		if (!($this->_route instanceof RM_Routing)) {
			$this->_route = RM_Routing::getById( $this->getIdRoute() );
		}
		return $this->_route;
	}
	
	public function isShow() {
		return $this->getStatus() === self::STATUS_SHOW;
	}

	public function getStatus() {
		return $this->pageStatus;
	}
	
	public function setStatus($status) {
		$status = (int)$status;
		if ($this->getStatus() !== $status) {
			$this->pageStatus = $status;
		}
	}
	
	public function getType() {
		return $this->pageType;
	}
	
	public function show() {
		if ($this->getStatus() !== self::STATUS_SHOW) {
			$this->setStatus(self::STATUS_SHOW);
			$this->save();
		}
	}
	
	public function hide() {
		if ($this->getStatus() !== self::STATUS_HIDE) {
			$this->setStatus(self::STATUS_HIDE);
			$this->save();
		}
	}

	public function remove() {
		$this->setStatus(self::STATUS_DELETED);
		$this->getContentManager()->remove();
		$this->getRoute()->remove();
		$this->save();
	}
	
	/**
	 * @static
	 * @return Zend_Db_Select
	 */
	protected static function _getSelect() {
		$select = self::getDb()->select();
		$select->from(RM_Page::TABLE_NAME, RM_Page::_getDbAttributes());
		$select->where('pages.pageStatus != ?', self::STATUS_DELETED);
		$select->join(
			static::TABLE_NAME,
			'pages.idPage = ' . static::TABLE_NAME . '.idPage',
			static::_getDbAttributes()
		);
		return $select;
	}

}