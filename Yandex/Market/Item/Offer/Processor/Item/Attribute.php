<?php
class RM_Yandex_Market_Item_Offer_Processor_Item_Attribute
    extends
        RM_Yandex_Market_Item_Offer_Processor_Item_Abstract {

    /**
     * @param $offer
     * @param SimpleXMLElement $element
     */
    public function add($offer, SimpleXMLElement $element) {
        $value = $offer->{ $this->_element->getMethodName() }();
        if (!is_null($value)) {
            $element->addAttribute(
                $this->_element->getAttributeName(),
                $this->__prepareValue($value)
            );
        }
    }

}