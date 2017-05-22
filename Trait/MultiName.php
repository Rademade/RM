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
        foreach ($this->getContentManager()->getAllContentLangs() as $contentLang) {
            if ($contentLang->getFieldContent('name') != '') {
                return $contentLang->getFieldContent('name');
            }
        }
        return '';
    }

    protected function __getCurrentName() {
        $lang = $this->getContentManager()->getCurrentContentLang();
        return $lang ? $lang->getFieldContent('name') : '';
    }

    protected function __getDefaultName() {
        $lang = $this->getContentManager()->getDefaultContentLang();
        return $lang ? $lang->getFieldContent('name') : '';
    }

}