<?php
interface RM_Interface_Contentable {

    public function getIdContent();

    public function setContentManager(RM_Content $contentManager);

    /**
     * @abstract
     * @return RM_Content
     */
    public function getContentManager();

    /**
     * @abstract
     * @return RM_Content_Lang
     */
    public function getDefaultContent();

    /**
     * @abstract
     * @return RM_Content_Lang
     */
    public function getContent();

}