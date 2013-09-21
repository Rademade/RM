<?php
trait RM_Trait_Alias {

    /**
     * @var Zend_Cache_Core
     */
    private static $_cache;

    /**
     * cache name is same
     * @abstract
     * @throws Exception
     * @return string
     */
    protected static function _getAliasFieldName() {
        throw new Exception('Implement _getAliasFieldName() in ' . get_called_class());
    }

    abstract public function getAlias();
    abstract public function getName();
    abstract protected function __setAlias($alias);

    abstract public function getId();

    /**
     * @param $alias
     *
     * @return static
     */
    final public static function getByAlias($alias) {
        $preparedAlias = self::_prepareAlias($alias);
        $result = static::getCacher()->load($preparedAlias);
        if (!$result instanceof static) {
            $result = static::findOne(array(
                static::_getAliasFieldName() => $alias
            ));
            static::getCacher()->cache($result, $preparedAlias);
        }
        return $result;
    }

    protected function _generateAlias() {
        $alias = $this->_getFormattedAlias();
        if (!$this->_isUniqueAlias($alias)) {
            throw new Exception(get_called_class() . ' with such alias already exist');
        }
        $this->_updateAlias($alias);
    }

    protected function _getFormattedAlias() {
        $url = new RM_Routing_Url($this->getName());
        $url->formatLikeAlias();
        return $url->getInitialUrl();
    }

    protected function __refreshAliasCache() {
        static::getCacher()->cache($this, self::_prepareAlias($this->getAlias()));
    }

    protected function __cleanAliasCache() {
        static::getCacher()->remove(self::_prepareAlias($this->getAlias()));
    }

    private static function _prepareAlias($alias) {
        return preg_replace('/[^\w\d\_]+/', '_', $alias);
    }

    private function _isUniqueAlias($alias) {
        $aliasModel = static::getByAlias($alias);
        return !($aliasModel instanceof static && $aliasModel->getId() !== $this->getId());
    }

    private function _updateAlias($alias) {
        if ($this->getAlias() != '' && $this->getAlias() != $alias) {
            $this->__cleanAliasCache();
        }
        $this->__setAlias($alias);
    }

}