<?php

/**
 * Class RM_Cassandra_Session
 * @method getData
 * @method setData
 *
 * @method setModifiedAt
 * @method getModifiedAt
 *
 * @method getLifetime
 * @method setLifetime
 */
class RM_Cassandra_Session
    extends
        RM_Cassandra_Entity {

    const TABLE_NAME = 'SessionStore';
    const ID_TYPE = self::AS_STRING;

    public static $attributesDefinition = [
        'modifiedAt'    => self::AS_INTEGER,
        'lifetime'      => self::AS_INTEGER,
        'data'          => self::AS_STRING
    ];

}