<?php
class RM_View_Form_Field_Value {

    private $langs = array();
    private $value = '';
    private $values = array();
    
    private $type = null;
    
    const TYPE_SIMPLE = 1; 
    const TYPE_MULTI_LANG = 2;
    
    public function __construct($value) {
        if (is_array($value)) {
            $this->type = self::TYPE_MULTI_LANG;
            $this->parseValues($value);
        } else {
            $this->type = self::TYPE_SIMPLE;
            $this->value = $value;
        }
    }
    
    private function parseValues($values) {
        foreach ($values as $idLang => $value) {
            $idLang = (int)$idLang;
            $this->langs[] = RM_Lang::getById($idLang);
            $this->values[ $idLang ] = $value;
        }
    }
    
    private function getType() {
        return $this->type;
    }
    
    public function getLangs() {
        return $this->langs;
    }
    
    public function isMultiLang() {
        return $this->getType() === self::TYPE_SIMPLE;
    }
    
    public function getContent($idLang) {
        switch ($this->getType()) {
            case self::TYPE_SIMPLE:
                return $this->value;
                break;
            case self::TYPE_MULTI_LANG:
                if (!isset($this->values[ $idLang ])) {
                    $this->values[ $idLang ] = '';
                }
                return $this->values[ $idLang ];
                break;
        }
    }

}