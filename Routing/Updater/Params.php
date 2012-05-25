<?php
class RM_Routing_Updater_Params {

    /**
     * @static
     * @return Zend_Db_Adapter_Abstract
     */
    public static function _getDb() {
        return Zend_Registry::get('db');
    }

    public function _getRoutesArray() {
        $select = self::_getDb()->select();
        /* @var Zend_Db_Select $select */
        $select->from( RM_Routing::TABLE_NAME );
        return self::_getDb()->fetchAll( $select );
    }

    public function updateParams() {
        foreach ($this->_getRoutesArray() as $routeData) {
            $params = @unserialize( $routeData->defaultParams );
            if (is_array($params)) {
                self::_getDb()->update(
                    RM_Routing::TABLE_NAME,
                    array(
                        'defaultParams' => json_encode( $params )
                    ),
                    'idRoute = ' . $routeData->idRoute
                );
            }
        }
    }

}