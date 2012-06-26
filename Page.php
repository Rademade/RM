<?php
class RM_Page
	extends
		RM_Entity
	implements
		RM_Interface_Switcher,
		RM_Interface_Hideable,
		RM_Interface_Deletable,
		RM_Interface_Contentable {

	const CACHE_NAME = 'page';

	const TABLE_NAME = 'pages';

	protected static $_properties = array(
		'id' => array(
			'id' => true,
			'field' => 'idPage',
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
		'systemStatus' => array(
			'default' => self::TURN_OFF,
			'type' => 'int'
		),
		'pageType' => array(
			'type' => 'int'
		)
	);

	/**
	 * @var RM_Entity_Worker_Data
	 */
	private $_dataWorker;

	/**
	 * @var RM_Entity_Worker_Cache
	 */
	protected $_cacheWorker;

    /**
	 * @var RM_Content
	 */
	private $_content;

    /**
	 * @var RM_Routing
	 */
	private $_route;

    /**
     * @var Application_Model_System_Block[]
     */
    private $_rightBlock;

	const TYPE_PAGE = 1;
	const TYPE_CATEGORY = 2;
    const TYPE_COUNTRY = 3;

	public function __construct($data) {
		$this->_dataWorker = new RM_Entity_Worker_Data(get_class(), $data);
		$this->_cacheWorker = new RM_Entity_Worker_Cache(get_class());
	}

	protected function __setPageData( $controller, $action, $url) {
		$route = RM_Routing::create(
			RM_Routing::TMP_ROUTE_NAME,
			$controller,
			$action,
			$url
		);
		$this->setRoute( $route );
		$this->setContentManager( RM_Content::create() );
	}

    public function getId() {
        return $this->_dataWorker->_getKey()->getValue();
    }

    public function __get($name) {
        $val = $this->_dataWorker->getValue($name);
        if (is_null($val)) {
            throw new Exception("Try to get unexpected attribute {$name}");
        } else {
            return $val;
        }
    }

    public function __set($name, $value) {
        if (is_null($this->_dataWorker->setValue($name, $value))) {
            throw new Exception("Try to set unexpected attribute {$name}");
        }
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
		if (intval($this->getRoute()->idPage) != $this->getIdPage()) {
			if ($this->getRoute()->getName() === RM_Routing::TMP_ROUTE_NAME) {
				$this->getRoute()->setName( $this->getIdPage() );
			}
			$this->getRoute()->idPage = $this->getIdPage();
			$this->getRoute()->save();
		}
	}

	public function save() {
        $this->_dataWorker->setValue('idContent', $this->getContentManager()->save()->getId());
        $this->_dataWorker->setValue('idRoute', $this->getRoute()->save()->getId());
		if ($this->_dataWorker->save() && static::AUTO_CACHE) {
			$this->__refreshCache();
		}
		$this->saveRoteDate();
	}

	public function getIdPage() {
		return $this->_dataWorker->getValue('id');
	}

	public function getIdContent() {
		return $this->_dataWorker->getValue('idContent');
	}

	public function setContentManager(RM_Content $contentManager) {
		if ($this->getIdContent() !== $contentManager->getId()) {
            $this->_dataWorker->setValue('idContent', $contentManager->getId());
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
        return $this->_dataWorker->getValue('idRoute');
	}

	public function setRoute(RM_Routing $route) {
		if ($this->getIdRoute() !== $route->getId()) {
            $this->_dataWorker->setValue('idRoute', $route->getId());
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
		return $this->getStatus() === self::STATUS_SHOW || $this->isSystem();
	}

	public function getStatus() {
        return $this->_dataWorker->getValue('pageStatus');
	}

	public function setStatus($status) {
        $status = (int)$status;
        if (in_array($status, array(
            self::STATUS_SHOW,
            self::STATUS_HIDE,
            self::STATUS_DELETED
        ))) {
            $this->_dataWorker->setValue('pageStatus', $status);
        } else {
            throw new Exception('Wrong status given');
        }
	}

	public function getType() {
        return $this->_dataWorker->getValue('pageType');
	}

	public function show() {
		if ($this->getStatus() !== self::STATUS_SHOW) {
			$this->setStatus(self::STATUS_SHOW);
			$this->save();
		}
	}

	public function hide() {
		if ($this->isSystem()) {
			throw new Exception('Can not hide system page');
		}
		if ($this->getStatus() !== self::STATUS_HIDE) {
			$this->setStatus(self::STATUS_HIDE);
			$this->save();
		}
	}

	public function remove() {
		if ($this->isSystem()) {
			throw new Exception('Can not delete system page');
		}
		$this->setStatus(self::STATUS_DELETED);
		$this->save();
		$this->getContentManager()->remove();
		$this->getRoute()->remove();
		$this->__cleanCache();
	}


	public function setSystem($status) {
        $this->_dataWorker->setValue('systemStatus', $status ? self::TURN_ON : self::TURN_OFF);
	}

	public function isSystem() {
		return $this->_dataWorker->getValue('systemStatus') === self::TURN_ON;
	}

	/**
	 * @static
	 * @param $select Zend_Db_Select
	 */
	public static function _setSelectRules(Zend_Db_Select $select) {
		$select->where('pages.pageStatus != ?', self::STATUS_DELETED);
	}


    public function getRightBlock() {
        if ( !is_array($this->_rightBlock) ) {
            $this->_rightBlock = RM_Block_Repository::getShowedBlocks(
                $this->getIdPage(),
                RM_Block::SEARCH_TYPE_RIGHT_BLOCK
            );
        }
        return $this->_rightBlock;
    }

}