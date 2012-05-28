<?php
/**
 * @property int idPage
 * @property int idCategory
 * @property int page
 * @property int idContentPage
 * @property int routeStatus
 * @property int idRoute
 * @property int type
 * @property string name
 * @property string action
 * @property string url
 * @property string controller
 * @property string module
 * @property string defaultParams
 */
class RM_Routing
    extends
        RM_Entity
	implements
		RM_Interface_Deletable {

    const TABLE_NAME = 'routing';

    const CACHE_NAME = 'routing';

    const AUTO_CACHE = true;

    protected static $_properties = array(
        'idRoute' => array(
            'id' => 'true',
            'type' => 'int'
        ),
        'type' => array(
            'default' => self::TYPE_ROUTE,
            'type' => 'int'
        ),
        'name' => array(
            'type' => 'string'
        ),
        'module' => array(
            'default' => 'public',
            'type' => 'string'
        ),
        'controller' => array(
            'type' => 'string'
        ),
        'action' => array(
            'type' => 'string'
        ),
        'url' => array(
            'type' => 'string'
        ),
        'defaultParams' => array(
            'default' => '{}',
            'type' => 'string'
        ),
        'routeStatus' => array(
            'default' => self::STATUS_UNDELETED,
            'type' => 'int'
        )
    );

	/**
	 * @var RM_Routing_Url
	 */
	private $_url;
	/**
	 * @var RM_Routing_DefaultParams
	 */
	private $_defaultParams;
    /**
     * @var RM_Entity_Worker_Data
     */
    private $_dataWorker;

    private $_calledClass;

	const TYPE_ROUTE  = 1;
	const TYPE_STATIC = 2;
	const TYPE_REGEX = 3;
	
	const ERROR_INVALID_ROUTE_ID = 'Invalid route id';
	const ERROR_ALIAS = 'Wrong format of page alias';
	const ERROR_ALIAS_EXISTS = 'Such alias already exists';
    const ERROR_INVALID_ROUTE_TYPE = 'Invalid route type';
	
	const ROUTE_CACHE = 'ALL';

	const TMP_ROUTE_NAME = '~tmp';

	public function __construct(stdClass $data) {
        parent::__construct($data);
        $this->_url = new RM_Routing_Url( $data->url );
        $this->_defaultParams = new RM_Routing_DefaultParams($data->defaultParams);
        $this->_calledClass = get_called_class();
        $this->_dataWorker = new RM_Entity_Worker_Data($this->_calledClass, $data);
	}
	
	public static function create(
		$routeName,
		$routeController,
		$routeAction,
		$routeUrl
	) {
		$url = new RM_Routing_Url($routeUrl);
		$route = new self( new RM_Compositor( array(
            'name' => $routeName,
            'controller' => $routeController,
            'action' => $routeAction,
            'url' => $url->format()->getInitialUrl(),
            'defaultParams' => '{}'
        ) ) );
		return $route;
	}

	public static function getByUrl($url) {
		$url = trim($url);
        $select = self::_getSelect();
        $select->where( 'url = ?', $url);
        return self::_initItem( $select );
	}

	public function validate(RM_Exception $e) {
		if (!$this->getRoutingUrl()->checkFormat( $this->getParams() )) {
			$e[] = self::ERROR_ALIAS;
		}
		if (!$this->getRoutingUrl()->checkUnique( $this->getId() )) {
			$e[] = self::ERROR_ALIAS_EXISTS;
		}
	}
	
	public function getId() {
		return $this->idRoute;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getStatus() {
		return $this->routeStatus;
	}
	
	/**
	 * @param int $status
	 * @throws Exception
	 * @return RM_Routing
	 */
	public function setStatus($status) {
		$status = (int)$status;
		if (in_array($status, array(
			self::STATUS_DELETED,
			self::STATUS_UNDELETED,
		))) {
            $this->routeStatus = $status;
		} else {
			throw new Exception('WRONG STATUS GIVEN');
        }
		return $this;
	}
	
	public function setName($name) {
        $this->name = $name;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function setAction($actionName) {
        if ($actionName === '') {
            throw new Exception('Empty action name given');
        }
        $this->action = $actionName;
	}

    /**
     * @return RM_Routing_Url
     */
	public function getRoutingUrl() {
		return $this->_url;
	}

	public function getUrl(array $params = array()) {
		$params = array_merge($this->_defaultParams->getParams(), $params);
		return $this->getRoutingUrl()->getAssembledUrl( $params );
	}

	public function setUrl($url) {
		if ($this->getRoutingUrl()->getInitialUrl() !== $url) {
			$urlObject = new RM_Routing_Url($url);
			$urlObject->format();
			$this->_url = $urlObject;
			$this->url = $urlObject->getInitialUrl();
		}
	}

    public function __get($name) {
        $val = $this->_dataWorker->getValue($name);
        if (is_null($val)) {
            return $this->_defaultParams->__get($name);
        } else {
            return $val;
        }
    }

    public function __set($name, $value) {
        if (is_null($this->_dataWorker->setValue($name, $value))) {
            $this->_defaultParams->__set($name, $value);
            $this->defaultParams = $this->_defaultParams->__toString();
        }
    }

    public function save() {
        if ($this->_dataWorker->save()) {
            static::clearCache();
            if (static::AUTO_CACHE) {
                $this->__refreshCache();
            }
        }
        return $this;
    }

	public function getParams() {
		return array_merge(array(
			'controller' => $this->controller,
			'action' => $this->action,
			'module' => $this->module,
			'idRoute' => $this->idRoute
		), $this->_defaultParams->getParams());
	}

    /**
     * @deprecated
     */
    public function delete() {
        $this->remove();
    }

	public function remove(){
		$this->setStatus( self::STATUS_DELETED );
		$this->save();
		$this->clear();
	}

    public function clear() {
        $cm = Zend_Registry::get('cachemanager');
        /* @var Zend_Cache_Manager $cm */
        $cache = $cm->getCache('routing');
        /* @var Zend_Cache_Core $cache */
        $cache->remove( $this->getId() );
    }

	/**
	 * @static
     * @deprecated
	 * @return RM_Routing[]
	 */
	public static function getRoutingList() {
        return self::getList();
	}

    public static function _setSelectRules(Zend_Db_Select $select) {
        $select->where('routeStatus != ?', self::STATUS_DELETED);
    }

    /**
     * @deprecated
     */
	public static function clearCache() {
        RM_Routing_Installer::getInstance()->clear();
	}

}