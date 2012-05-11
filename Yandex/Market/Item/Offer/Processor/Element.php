<?php
class RM_Yandex_Market_Item_Offer_Processor_Element {

    private $_method;

    /**
     * @var
     */
    private $_docParams;

    public function __construct(ReflectionMethod $method) {
        $this->_method = $method;
    }

    private function _getDocParams() {
        if (is_null($this->_docParams)) {
            $params = array();
            if (preg_match_all(
                '/\@(.*)\ (.*)/i',
                $this->_method->getDocComment(),
                $data
            )) {
                $count = sizeof($data[1]);
                for ($i = 0; $i < $count; $i++) {
                    $params[ $data[1][$i] ] = $data[2][$i];
                }
            }
            $this->_docParams = (object)$params;
        }
        return $this->_docParams;
    }

    public function getMethodName() {
        return $this->_method->getName();
    }

    public function isAttribute() {
        return isset($this->_getDocParams()->attribute);
    }

    public function getAttributeName() {
        return $this->isAttribute() ? $this->_getDocParams()->attribute : null;
    }

    public function isTag() {
        return isset($this->_getDocParams()->tag);
    }

    public function getTagName() {
        return $this->isTag() ? $this->_getDocParams()->tag : null;
    }

}