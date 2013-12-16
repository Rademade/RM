<?php
interface RM_System_Sitemap_Item_Image
    extends
        RM_System_Sitemap_Item_Abstract {

    public function getUrl();
    /**
     * @return array
     */
    public function getImagePaths();

}