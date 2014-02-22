<?php
trait RM_Trait_MultiName {

    private $_cachedName;

    /**
     * @return RM_Content
     */
    abstract public function getContentManager();

    public function getName() {
        if ( is_string($this->_cachedName) ) {
            return $this->_cachedName;
        }
        $name = $this->__getCurrentName();
        if ($name == '') $name = $this->__getDefaultName();
        if ($name == '') $name = $this->getAnyName();
        $this->_cachedName = $name;
        return $name;
    }

    public function getAnyName() {
        foreach ($this->getContentManager()->getContentLangs() as $contentLang) {
            if ($contentLang->getFieldContent('name') != '') {
                return $contentLang->getFieldContent('name');
            }
        }
        return '';
    }

    protected function __getCurrentName() {
        return $this->getContentManager()->getCurrentContentLang()->getFieldContent('name');
    }

    protected function __getDefaultName() {
        return $this->getContentManager()->getDefaultContentLang()->getFieldContent('name');
    }

}