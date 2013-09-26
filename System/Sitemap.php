<?php
class RM_System_Sitemap {

    private $_urlPrefix;
    /**
     * @var RM_System_Sitemap_Item[]
     */
    private $_items = [];
    /**
     * @var SimpleXMLElement
     */
    private $_xml;

    public function __construct($prefix = '') {
        $this->_urlPrefix = $prefix;
        $this->_xml = new SimpleXMLElement(join('', array(
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            '</urlset>'
        )));
    }

    public function setItems(array $items) {
        $this->_items = $items;
    }
    
    public function addItem(RM_System_Sitemap_Item $item) {
        $this->_items[] = $item;
    }

    public function initXmlItems() {
        foreach ($this->_items as $item) {
            $xmlItem = $this->_xml->addChild('url');
            $xmlItem->addChild('loc', rtrim($this->_urlPrefix . $item->getUrl(), '/'));
            $xmlItem->addChild('priority', $item->getPriority());
        }
    }

    public function getXmlElement() {
        return $this->_xml;
    }

    public function getXML() {
        $this->initXmlItems();
        return $this->_xml->asXML();
    }

}