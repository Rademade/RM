<?php
class RM_Routing_Installer {

    const DEFAULT_ROUTER_CACHE_CORE_NAME = 'routing';
    const DEFAULT_ROUTE_CACHE_NAME = 'ALL';

    const ROUTER_CONFIG_DIR = '/configs/routes/';

    /**
     * @var RM_Routing_Installer
     */
    private static $_self;
    /**
     * @var Zend_Controller_Router_Rewrite
     */
    private $_router;
    /**
     * @var Zend_Cache_Core
     */
    private $_cacheCore;
    private $_routerCacheCoreName;
    private $_routeCacheName;

    private function __construct() {

    }

    /**
     * @static
     * @return static
     */
    public static function getInstance() {
        if (!self::$_self instanceof self) {
            self::$_self = new static();
        }
        return self::$_self;
    }

    /**
     * @param Zend_Controller_Router_Rewrite $router
     */
    public function setRouter(Zend_Controller_Router_Rewrite $router) {
        $this->_router = $router;
    }

    /**
     * @return Zend_Controller_Router_Rewrite
     * @throws Exception
     */
    public function getRouter() {
        if (!$this->_router instanceof Zend_Controller_Router_Rewrite) {
            throw new Exception('Zend_Controller_Router_Abstract was not set');
        }
        return $this->_router;
    }

    /**
     * @return Zend_Cache_Core
     */
    protected function __getCacheCore() {
        if (!$this->_cacheCore instanceof Zend_Cache_Core) {
            $cacheManager = Zend_Registry::get('cachemanager');
            /* @var Zend_Cache_Manager $cacheManager */
            $this->_cacheCore = $cacheManager->getCache($this->_getRouterCacheCoreName());
        }
        return $this->_cacheCore;
    }

    /**
     * @return Zend_Controller_Router_Route_Abstract[]
     */
    protected function __getCachedRouter() {
        return $this->__getCacheCore()->load($this->_getRouteCacheName());
    }

    /**
     * Add to current router config.ini files
     */
    protected function __installFileRouter() {
        $dir = APPLICATION_PATH . self::ROUTER_CONFIG_DIR;
        $handle = opendir($dir);
        while (false !== ($file = readdir($handle))) {
            if (preg_match('/\.ini/', $file)) {
                $this->__addRouteFile($dir . $file);
            }
        }
    }

    protected function __addRouteFile($file) {
        $config = new Zend_Config_Ini($file, APPLICATION_ENV);
        $this->getRouter()->addConfig($config);
    }

    /**
     * @return RM_Routing[]
     */
    protected function __getRoutes() {
        return RM_Routing::getList();
    }

    /**
     * Add to current router custom database routes
     * @throws Exception
     */
    protected function __installDbRouter() {
        foreach ($this->__getRoutes() as $route) {
            switch ($route->getType()) {
                case RM_Routing::TYPE_ROUTE:
                    $this->__addTypeRoute($route);
                    break;
                default:
                    throw new Exception(RM_Routing::ERROR_INVALID_ROUTE_TYPE);
                    break;
            }
        }
    }

    /**
     * Add Zend_Controller_Router_Route route type to current router
     * @param RM_Routing $route
     */
    protected function __addTypeRoute(RM_Routing $route) {
        $this->getRouter()->addRoute(
            $route->getName(),
            new Zend_Controller_Router_Route(
                $route->getRoutingUrl()->getInitialUrl(),
                $route->getParams()
            )
        );
    }

    /**
     * Install all router to current router
     * @return void
     */
    public function install() {
        if ($routes = $this->__getCachedRouter()) {
            $this->getRouter()->addRoutes($routes);
        } else {
            $this->__installFileRouter();
            $this->__installDbRouter();
            $this->__getCacheCore()->save($this->getRouter()->getRoutes());
        }
    }

    public function clear() {
        self::__getCacheCore()->remove($this->_getRouteCacheName());
    }

    public function setRouterCacheCoreName($name) {
        $this->_routerCacheCoreName = $name;
    }

    private function _getRouterCacheCoreName() {
        return $this->_routerCacheCoreName ? : static::DEFAULT_ROUTER_CACHE_CORE_NAME;
    }

    public function setRouteCacheName($name) {
        $this->_routeCacheName = $name;
    }

    private function _getRouteCacheName() {
        return $this->_routeCacheName ? : static::DEFAULT_ROUTE_CACHE_NAME;
    }

}