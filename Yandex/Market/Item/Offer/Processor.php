<?php
class RM_Yandex_Market_Item_Offer_Processor {

    private $_rc;

    /**
     * @var ReflectionClass[]
     */
    private $_usedInterfaces = array();

    /**
     * @var RM_Yandex_Market_Item_Offer_Processor_Item_Abstract[]
     */
    private $_items = array();

    const MARKET_INTERFACE_KEY = 'yandexMarketInterface';

    public function __construct($className) {
        $this->_rc = new ReflectionClass($className);
        $this->_collectUsedInterfaces();
        $this->_collectUsedMethodsData();
    }

    private function _collectUsedInterfaces() {
        foreach ( $this->_rc->getInterfaces() as $rcInterface) {
            /* @var ReflectionClass $rcInterface */
            if (preg_match('/\@' . self::MARKET_INTERFACE_KEY . '/', $rcInterface->getDocComment())) {
                $this->_usedInterfaces[] = $rcInterface;
            }
        }
    }

    private function _collectUsedMethodsData() {
        foreach ($this->_usedInterfaces as $rcInterface) {
            foreach ($rcInterface->getMethods() as $method) {
                $element = new RM_Yandex_Market_Item_Offer_Processor_Element($method);
                if ($element->isAttribute()) {
                    $this->_items[] = new RM_Yandex_Market_Item_Offer_Processor_Item_Attribute( $element );
                }
                if ($element->isTag()) {
                    $this->_items[] = new RM_Yandex_Market_Item_Offer_Processor_Item_Tag( $element );
                }
            }
        }
    }

    public function setParams($offer, SimpleXMLElement $element) {
        foreach ($this->_items as $item) {
            $item->add($offer, $element);
        }
    }

}