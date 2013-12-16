<?php
abstract class RM_System_Sitemap_Abstract {

    protected $_urlPrefix;
    /**
     * @var RM_System_Sitemap_Item_Abstract[]
     */
    protected $_items = [];
    /**
     * @var SimpleXMLElement
     */
    protected $_xml;

    abstract public function initXmlItems();
    abstract protected function __getRootElement();

    public function __construct($prefix = '') {
        $this->_urlPrefix = $prefix;
        $this->_xml = $this->__getRootElement();
    }

    public function setItems(array $items) {
        $this->_items = $items;
    }

    public function addItem(RM_System_Sitemap_Item_Abstract $item) {
        $this->_items[] = $item;
    }

    public function getXmlElement() {
        return $this->_xml;
    }

    public function getXML() {
        $this->initXmlItems();
        return $this->_xml->asXML();
    }

}