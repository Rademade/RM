<?php
class RM_System_Sitemap_Image
    extends
        RM_System_Sitemap_Abstract {

    public function initXmlItems() {
        foreach ($this->_items as $item) {
            /* @var RM_System_Sitemap_Item_Image $item */
            $xmlItem = $this->_xml->addChild('url');
            $xmlItem->addChild('loc', rtrim($this->_urlPrefix . $item->getUrl(), '/'));
            foreach ($item->getImagePaths() as $imagePath) {
                $imageXml = $xmlItem->addChild('image:image');
                $imageXml->addChild('image:loc', $imagePath);
            }
        }
    }

    protected function __getRootElement() {
        return new SimpleXMLElement(join('', array(
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">',
            '</urlset>'
        )));
    }

}