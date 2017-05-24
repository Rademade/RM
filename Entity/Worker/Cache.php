<?php
class RM_Entity_Worker_Cache
    implements
        Serializable {
    
    /**
     * @var Zend_Cache_Core
     */
    private $_cache;
    
    private $_cacheName;
    
    public function __construct($className) {
        $this->_cacheName = $className::CACHE_NAME;
        $this->_initCache();
    }
    
    private function _initCache() {
        /* @var $cm Zend_Cache_Manager */
        if (!is_null($this->_cacheName)) {
            $this->_cache = Zend_Registry::get('cachemanager')->getCache( $this->_cacheName );
        }
    }
    
    public function clean($tag) {
        if ($this->_cache instanceof Zend_Cache_Core) {
            $type = empty($tag) ? Zend_Cache::CLEANING_MODE_ALL : Zend_Cache::CLEANING_MODE_MATCHING_TAG;
            $tags = is_array($tag) ? $tag : array($tag);
            $this->_cache ->clean($type, $tags);
        }
    }
    
    public function cache($data, $key, $tags = array()) {
        if ($this->_cache instanceof Zend_Cache_Core) {
            $this->_cache->save( $data, $key, $tags);
        }
    }
    
    public function load($key) {
        if ($this->_cache instanceof Zend_Cache_Core) {
            $data = $this->_cache->load($key);
            if ($data !== false) {
                return $data;
            }
        }
        return null;
    }
    
    public function remove($key) {
        if ($this->_cache instanceof Zend_Cache_Core) {
            $this->_cache->remove($key);
        }
    }
    
    public function serialize() {
        return $this->_cacheName;
    }
    
    public function unserialize($cacheName) {
        $this->_cacheName = $cacheName;
        $this->_initCache();
    }

}