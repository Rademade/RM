<?php
trait RM_Trait_Cache {

    private $_cache;

    protected function __getCachedValue($key, Closure $function) {
        $validKey = $this->_removeInvalidCharacters($key);
        $cachedValue = $this->_getCache()->load($validKey);
        if ($cachedValue === false) {
            $cachedValue = $function();
            $this->_getCache()->save($cachedValue, $validKey);
        }
        return $cachedValue;
    }

    protected function __getCacheName() {
        return 'default';
    }

    private function _removeInvalidCharacters($key) {
        return preg_replace('/([^a-zA-Z0-9_])/', '', $key);
    }

    private function _getCache() {
        if (!$this->_cache instanceof Zend_Cache_Core) {
            $this->_cache = Zend_Registry::get('cachemanager')->getCache($this->__getCacheName());
        }
        return $this->_cache;
    }

}