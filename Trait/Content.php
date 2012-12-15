<?php
trait RM_Trait_Content {

    /**
     * @var RM_Content
     */
    private $_content;

    abstract public function getIdContent();

    abstract protected function __setIdContent($idContent);

    /**
     * @param RM_Content $contentManager
     * @return \RM_Trait_Content
     */
    public function setContentManager(RM_Content $contentManager) {
        $this->__setIdContent( $contentManager->getId() );
        $this->_content = $contentManager;
        return $this;
    }

    /**
     * @return RM_Content
     */
    public function getContentManager() {
        if (!$this->_content instanceof RM_Content) {
            $this->_content = RM_Content::getById( $this->getIdContent() );
        }
        return $this->_content;
    }

    /**
     * @return RM_Content_Lang
     */
    public function getDefaultContent() {
        return $this->getContentManager()->getDefaultContentLang();
    }

    /**
     * @return RM_Content_Lang
     */
    public function getContent() {
        return $this->getContentManager()->getCurrentContentLang();
    }

}
