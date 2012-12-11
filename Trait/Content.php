<?php
trait RM_Trait_Content {

    /**
     * @var RM_Content
     */
    private $_content;

    abstract public function getIdContent();

    abstract protected function __setIdContent($idContent);

    public function setContentManager(RM_Content $contentManager) {
        $this->__setIdContent( $contentManager->getId() );
        $this->_content = $contentManager;
    }

    public function getContentManager() {
        if (!$this->_content instanceof RM_Content) {
            $this->_content = RM_Content::getById( $this->getIdContent() );
        }
        return $this->_content;
    }

    public function getDefaultContent() {
        return $this->getContentManager()->getDefaultContentLang();
    }

    public function getContent() {
        return $this->getContentManager()->getCurrentContentLang();
    }

}
