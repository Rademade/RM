<?php
abstract class RM_Yandex_Market_Item_Offer_Processor_Item_Abstract {

    /**
     * @var RM_Yandex_Market_Item_Offer_Processor_Element
     */
    protected $_element;

    public function __construct(RM_Yandex_Market_Item_Offer_Processor_Element $element) {
        $this->_element = $element;
    }

    /**
     * @abstract
     * @param $offer
     * @param SimpleXMLElement $element
     * @return mixed
     */
    abstract public function add($offer, SimpleXMLElement $element);

    private function _replaceWrongChars($str) {
        $str = str_replace(array(
            chr(13),
            "\n"
        ), '', $str);
        return $str;
    }

    /**
     * @param $val
     * @return string
     * @throws Exception
     */
    protected function __prepareValue($val) {
        switch (gettype($val)) {
            case 'boolean':
                return $val ? 'true' : 'false';
                break;
            case 'integer':
            case 'double':
            case 'float':
                return $val;
                break;
            case 'string':
                return trim( $this->_replaceWrongChars( htmlspecialchars( strip_tags($val) ) ) );
                break;
            default:
                throw new Exception('Wrong type given');
                break;
        }
    }

}