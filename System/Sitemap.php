<?php
class RM_System_Sitemap
    extends
        RM_System_Sitemap_Abstract {

    public function initXmlItems() {
        foreach ($this->_items as $item) {
            /* @var RM_System_Sitemap_Item $item */
            $xmlItem = $this->_xml->addChild('url');
            $xmlItem->addChild('loc', rtrim($this->_urlPrefix . $item->getUrl(), '/'));
            $xmlItem->addChild('priority', $item->getPriority());
        }
    }

    protected function __getRootElement() {
        return new SimpleXMLElement(join('', array(
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            '</urlset>'
        )));
    }

}