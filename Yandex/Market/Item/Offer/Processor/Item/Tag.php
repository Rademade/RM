<?php
class RM_Yandex_Market_Item_Offer_Processor_Item_Tag
    extends
        RM_Yandex_Market_Item_Offer_Processor_Item_Abstract {

    /**
     * @param $offer
     * @param SimpleXMLElement $element
     */
    public function add($offer, SimpleXMLElement $element) {
        $result = $offer->{ $this->_element->getMethodName() }();
        if (!is_null($result)) {
            if (is_array($result)) {
                $this->_setArrayTag(
                    $this->_element->getTagName(),
                    $result,
                    $element
                );
            } else {
                $element->addChild(
                    $this->_element->getTagName(),
                    $this->__prepareValue($result)
                );
            }
        }
    }

    /**
     * @param $tag
     * @param array $data
     * @param SimpleXMLElement $element
     */
    private function _setArrayTag($tag, array $data, SimpleXMLElement $element) {
        foreach ($data as $val) {
            if (is_array($val)) {
                if (isset($val['value'])) {
                    $this->_setArrayTagParams($tag, $val, $element);
                } else {
                    $this->_setArrayTag($tag, $data, $element);
                }
            } else {
                $element->addChild($tag, $this->__prepareValue($val));
            }
        }
    }

    /**
     * @param $tag
     * @param array $tagParams
     * @param SimpleXMLElement $element
     */
    private function _setArrayTagParams($tag, array $tagParams, SimpleXMLElement $element) {
        $childElement = $element->addChild($tag, $this->__prepareValue( $tagParams['value'] ));
        unset( $tagParams['value'] );
        foreach ($tagParams as $key => $val) {
            $childElement->addAttribute($key, $val);
        }
    }

}