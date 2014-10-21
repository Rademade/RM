<?php
use RM_Cassandra_Query_ValueDecorator as ValueDecorator;

trait RM_Cassandra_Trait_EntityTypeOfAttributes {

    public static $attributesDefinition = [];

    public function typeCastAttribute($attrName, $attrValue) {
        $type = rm_isset(static::$attributesDefinition, $attrName);
        return $this->typeCast($attrValue, $type);
    }

    public function typeCast($value, $type) {
        switch ($type) {
            case ValueDecorator::AS_UUID:
            case ValueDecorator::AS_STRING:
                return (string)$value;

            case ValueDecorator::AS_BOOLEAN:
                return (bool)$value;

            case ValueDecorator::AS_INTEGER:
                return (int)$value;

            default:
                return $value;
        }
    }

}