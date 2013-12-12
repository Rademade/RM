<?php
//RM_TODO Rename! to RM_System_Sitemap_Item_Core
interface RM_System_Sitemap_Item
    extends
        RM_System_Sitemap_Item_Abstract {

    public function getUrl();
    public function getPriority();

}