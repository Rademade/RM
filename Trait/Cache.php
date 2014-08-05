<?php
trait RM_Trait_Cache {

    private static $_traitCacheManager;

    protected static function __getCachedValue($key, Closure $function) {
        $validKey = self::_removeInvalidCharacters($key);
        $cachedValue = self::_getCache()->load($validKey);
        if ($cachedValue === false) {
            $cachedValue = $function();
            self::_getCache()->save($cachedValue, $validKey);
        }
        return $cachedValue;
    }

    protected static function __cleanCachedValue($key) {
        $validKey = self::_removeInvalidCharacters($key);
        self::_getCache()->remove($validKey);
    }

    private static function _removeInvalidCharacters($key) {
        return preg_replace('/([^a-zA-Z0-9_])/', '', $key);
    }

    /**
     * @return Zend_Cache_Core
     */
    private static function _getCache() {
        if (!self::$_traitCacheManager instanceof Zend_Cache_Core) {
            self::$_traitCacheManager = Zend_Registry::get('cachemanager')->getCache(static::_getCacheName());
        }
        return self::$_traitCacheManager;
    }

    protected static function _getCacheName() {
        return 'default';
    }

    /**
     * @deprecated
     * @return string
     */
    protected function __getCacheName() {
       return $this->_getCacheName();
    }

}