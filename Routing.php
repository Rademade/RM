<?php
/**
 * @property int idPage
 * @property int idCategory
 * @property int page
 * @property mixed idContentPage
 */
class RM_Routing
	implements
		RM_Interface_Deletable {
	
	private $_idRoute;
	private $_type;
	private $_name;
	private $_module;
	private $_controller;
	private $_action;
	/**
	 * @var RM_Routing_Url
	 */
	private $_url;
	private $_routeStatus;
	/**
	 * @var RM_Routing_DefaultParams
	 */
	private $_defaultParams;
	
	private $_changes = array();
	
	const TYPE_ROUTE  = 1;
	const TYPE_STATIC = 2;
	const TYPE_REGEX = 3;
	
	const ERROR_INVALID_ROUTE_ID = 'Invalid route id';
	const ERROR_INVALID_ROUTE_TYPE = 'Invalid route type';
	const ERROR_ALIAS = 'Wrong format of page alias';
	const ERROR_ALIAS_EXISTS = 'Such alias already exists';
	
	const ROUTE_CACHE = 'ALL';

	public function __construct(
		$idRoute,
		$type,
		$name,
		$module,
		$controller,
		$action,
		RM_Routing_Url $url,
		RM_Routing_DefaultParams $defaultParams,
		$routeStatus
	) {
		$this->_idRoute = (int)$idRoute;
		$this->_type = (int)$type;
		$this->_name = $name;
		$this->_module = $module;
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_url = $url;
		$this->_defaultParams = $defaultParams;
		$this->_routeStatus = (int)$routeStatus;	
	}
	
	public static function create(
		$routeName,
		$routeController,
		$routeAction,
		$routeUrl
	) {
		$url = new RM_Routing_Url($routeUrl);
		$url->format();
		$params = new RM_Routing_DefaultParams();
		$route = new self(
			0,
			self::TYPE_ROUTE,
			$routeName,
			'public',
			$routeController,
			$routeAction,
			$url,
			$params,
			self::STATUS_UNDELETED
		);
		return $route;
	}

	private static function init($data) {
		return new self(
			$data->idRoute,
			$data->type,
			$data->name,
			$data->module,
			$data->controller,
			$data->action,
			new RM_Routing_Url($data->url),
			new RM_Routing_DefaultParams($data->defaultParams),
			$data->routeStatus
		);
	}
	
	private static function _getSelectWrapper($field, $value) {
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract */
		$select = $db->select()->from('routing');
		/* @var $select Zend_Db_Select */
		$select->where("routing.$field = ?", $value);
		$select->where('routing.routeStatus != ?', self::STATUS_DELETED);
		$select->limit(1);
		if (false !== ($data = $db->fetchRow($select))) {
			return self::init($data);
		} else {
			throw new Exception(self::ERROR_INVALID_ROUTE_ID);
		}
	}
	
	public static function getById($idRoute) {
		$idRoute = intval($idRoute);
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache('routing');
		if (($route = $cache->load($idRoute)) !== false) {
			return $route;
		} else {
			$route = self::_getSelectWrapper('idRoute', $idRoute);
			$cache->save($route);
			return $route;
		}
	}
	
	public static function getByUrl($url) {
		$url = trim($url);
		$key = md5($url);
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache('routing');
		if (($route = $cache->load($key)) !== false) {
			return $route;
		} else {
			$route = self::_getSelectWrapper('url', $url);
			$cache->save($route);
			return $route;
		}
	}

	public function validate(RM_Exception $e) {
		if (!$this->getRoutingUrl()->checkFormat( $this->getParams() )) {
			$e[] = self::ERROR_ALIAS;
		}
		if (!$this->getRoutingUrl()->checkUnique($this->_idRoute)) {
			$e[] = self::ERROR_ALIAS_EXISTS;
		}
	}
	
	public function getId() {
		return $this->_idRoute;
	}
	
	public function getType() {
		return $this->_type;	
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getStatus() {
		return $this->_routeStatus;
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
			if ($this->getStatus() !== $status) {
				$this->_routeStatus = $status;
				$this->_changes['routeStatus'] = $status;
			}
		} else
			throw new Exception('WRONG STATUS GIVEN');
		return $this;
	}
	
	public function setName($name) {
		if ($this->getName() !== $name) {
			$this->_name = $name;
			$this->_changes['name'] = $name;
		}
	}
	
	public function getAction() {
		return $this->_action;
	}
	
	public function setAction($actionName) {
		if ($this->getAction() !== $actionName) {
			if ($actionName === '') {
				throw new Exception('Empty action name given');
			}
			$this->_action = $actionName;
			$this->_changes['action'] = $actionName;
		}
	}
	
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
			$this->_changes['url'] = $urlObject->getInitialUrl();
		}
	}
	
	public function __set($param, $val) {
		if ($this->__get($param) !== $val) {
			$this->_defaultParams->__set($param, $val);
			$this->_changes['defaultParams'] = $this->_defaultParams->__toString();
		}
	}

	public function __get($param) {
		return $this->_defaultParams->__get($param);
	}

	public function getParams() {
		return array_merge(array(
			'controller' => $this->_controller,
			'action' => $this->_action,
			'module' => $this->_module,
			'idRoute' => $this->_idRoute
		), $this->_defaultParams->getParams());
	}
		
	public function save() {
		$db = Zend_Registry::get('db');
        /* @var $db Zend_Db_Adapter_Abstract */
		if ($this->getId() === 0) {
			$db->insert('routing', array(
				'type' => $this->getType(),
				'name' => $this->getName(),
				'module' => $this->_module,
				'controller' => $this->_controller,
				'action' => $this->_action,
				'url' => $this->getRoutingUrl()->getInitialUrl(),
				'defaultParams' => $this->_defaultParams->__toString(),
				'routeStatus' => $this->getStatus()
			));
			$this->_idRoute = (int)$db->lastInsertId();
			$this->_changes = array();
			self::clearCache();
		} else {
			if (!empty($this->_changes)) {
				$db->update('routing', $this->_changes, 'idRoute = ' . $this->getId());
				self::clearCache();
				$this->_changes = array();
				$this->clear();
			}
		}
		return $this;
	}

	public function remove(){
		$this->setStatus( self::STATUS_DELETED );
		$this->save();
		$this->clear();
	}

	/**
	 * @static
	 * @return RM_Routing[]
	 */
	public static function getRoutingList() {
		$db = Zend_Registry::get('db');
        /* @var $db Zend_Db_Adapter_Abstract */
		$select = $db->select()->from('routing');
		$select->where('routing.routeStatus != ?', self::STATUS_DELETED);
		$list = array();
		if (($result = $db->fetchAll($select)) !== false) {
			foreach ($result as $data) {
				$list[] = self::init($data);
			}
		}
		return $list;
	}
	
	public function clear() {
		Zend_Registry::get('cachemanager')
			->getCache('routing')
			->remove($this->getId());
	}

	public static function clearCache() {
		Zend_Registry::get('cachemanager')
			->getCache('routing')
			->remove(self::ROUTE_CACHE);
	}
	
	public static function installRouter(Zend_Controller_Router_Rewrite $router) {
		$cache = Zend_Registry::get('cachemanager')->getCache('routing');
		if (($list = $cache->load(self::ROUTE_CACHE)) !== false) {
			$router->addRoutes($list);
		} else {
			$dir = APPLICATION_PATH . '/configs/routes/';
			$handle = opendir($dir);
		 	while ( false !== ($file = readdir($handle)) ) {
		 		if (preg_match('/\.ini/', $file)) {
		 			$config = new Zend_Config_Ini($dir . $file, APPLICATION_ENV);
		 			$router->addConfig($config);
		 		}
		 	}			
			foreach (self::getRoutingList() as $route) {
				self::setRoute($router, $route);
			}
			$cache->save($router->getRoutes());
		}
	}
	
	private static function setRoute(
		Zend_Controller_Router_Rewrite $router,
		RM_Routing $route
	) {
		switch($route->getType()) {
			case self::TYPE_ROUTE:
				$router->addRoute(
					$route->getName(),
					new Zend_Controller_Router_Route($route->getRoutingUrl()->getInitialUrl(), $route->getParams())
				);
			break;
			default:
				throw new Exception(self::ERROR_INVALID_ROUTE_TYPE);
			break;
		}
	}

}