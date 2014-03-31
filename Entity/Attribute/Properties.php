<?php
class RM_Entity_Attribute_Properties {

    const TYPE_INT = 1;
    const TYPE_STRING = 2;
    const TYPE_FLOAT = 3;

	private $_name;
	private $_id;
    private $_ai;
	private $_field;
	private $_isColumn;
	private $_type;
	private $_default;

	public function __construct($name, array $settings) {
		$this->_name = $name;
		$this->_id = isset($settings['id']) && $settings['id'];
		$this->_type = $this->_defineType( $settings['type'] );
		$this->_field = isset($settings[ 'field' ]) ? $settings['field'] : null;
		$this->_isColumn = !(isset($settings[ 'column' ]) && $settings[ 'column' ] === false);
		$this->_default = isset($settings['default']) ? $settings['default'] : '';
        $this->_ai = isset($settings['ai']) ? $settings['ai'] : ($this->_id ? true : false);
	}

	public function getName() {
		return $this->_name;
	}

	public function isColumn() {
		return $this->_isColumn;
	}

	public function getFieldName() {
		return $this->_field ?: $this->getName();
	}

	public function getType() {
		return $this->_type;
	}

	public function isKey() {
		return $this->_id === true;
	}

    public function isAutoIncrement() {
        return $this->_ai;
    }

    public function removeAutoIncrement() {
        $this->_ai = false;
    }

	public function getDefault(){
		return $this->_default;
	}

    private function _defineType($typeString) {
        if ($typeString === 'int') {
            return self::TYPE_INT;
        } elseif ($typeString === 'string') {
            return self::TYPE_STRING;
        } elseif ($typeString === 'decimal' || $typeString === 'float') {
            return self::TYPE_FLOAT;
        } else {
            throw new Exception("Wrong type string given {$typeString}");
        }
    }

    public static function parseValue(self $prop, $value) {
        switch ($prop->getType()) {
            case self::TYPE_INT:
                return (int)$value;
            case self::TYPE_STRING:
                return (string)$value;
            case self::TYPE_FLOAT:
                return (float)$value;
        }
    }

}
