<?php
class RM_System_Sitemap {

    private $_urlPrefix;

    public function __construct($prefix = '') {
        $this->_urlPrefix = $prefix;
        $this->_xml = new SimpleXMLElement(join('', array(
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            '</urlset>'
        )));
    }

    public function addItem(RM_System_Sitemap_Item $item) {
        $xmlItem = $this->_xml->addChild('url');
        $xmlItem->addChild( 'loc', rtrim( $this->_urlPrefix . $item->getUrl(), '/') );
        $xmlItem->addChild( 'priority', $item->getPriority() );
    }

    public function getXML() {
        return $this->_xml->asXML();
    }

}