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

    private function _br2nl($string) {
        return preg_replace('#<br\s*?/?>#i', "\n", $string);
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
                return str_replace(
                    '&#13;',
                    '',
                    trim(
                        htmlspecialchars(
                            strip_tags(
                                $this->_br2nl($val
                                )
                            )
                        )
                    )
                );
                break;
            default:
                throw new Exception('Wrong type given');
                break;
        }
    }

}