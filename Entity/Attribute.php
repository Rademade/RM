<?php
class RM_Entity_Attribute {

	public static function parseValue(RM_Entity_Attribute_Properties $prop, $value) {
		$type = $prop->getType();
		if ($type === 'int') {
			return (int)$value;
		} elseif ($type === 'string') {
			return (string)$value;
		} elseif ($type === 'decimal' || $type === 'float') {
			return $value - 0.0;
		} else {
			throw new Exception("Unknown '{$prop->getFieldName()}' value type");
		}
	}

}