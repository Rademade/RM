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
    abstract protected function setAlias($alias);

    abstract public function getId();

    /**
     * @param $alias
     *
     * @return static
     */
    public static function getByAlias($alias) {
        $result = static::getCacher()->load(self::_prepareAlias($alias));
        if (!$result instanceof static) {
            $result = static::findOne(array(
                static::_getAliasFieldName() => $alias
            ));
            static::getCacher()->cache($result, self::_prepareAlias($alias));
        }
        return $result;
    }

    protected function _generateAlias() {
        $url = new RM_Routing_Url($this->getName());
        $url->formatLikeAlias();
        $alias = $url->getInitialUrl();
        if (!$this->_isUniqueAlias($alias)) {
            throw new Exception(get_called_class() . ' with such alias already exist');
        }
        $this->setAlias($alias);
    }

    protected function __refreshAliasCache() {
        static::getCacher()->cache($this, $this->getAlias());
    }

    protected function __cleanAliasCache() {
        static::getCacher()->remove($this->getAlias());
    }

    private function _isUniqueAlias($alias) {
        $aliasModel = static::getByAlias($alias);
        return !($aliasModel instanceof static && $aliasModel->getId() !== $this->getId());
    }

    private static function _prepareAlias($alias) {
        return preg_replace('/[^\w\d\_]+/', '_', $alias);
    }

}