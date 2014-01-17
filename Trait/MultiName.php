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
        $name = $this->getContentManager()->getDefaultContentLang()->getFieldContent('name');
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

}