<?php
class RM_Entity_Db {

    use RM_Trait_Singleton;

    const PRIMARY_DB = 'primary';

    const TYPE_SIMPLE = 'simple';
    const TYPE_PREFIX = 'prefix';

    private $_mapping = null;
    private $_connections = array();

    /**
     * @example of $mapping
     *  array(
     *      RM_Entity_Db::PRIMARY_DB => array(
     *          '/^Application_Model_\w+&/'
     *      ),
     *      'arredo' => array(
     *          '/^Arredo_Model_\w+$/',
     *          '/^RM_\w+$/'
     *      )
     *  )
     * @param array $mapping
     */
    public function setClassMapping(array $mapping) {
        $this->_mapping = array();
        foreach ($mapping as $db => $params) {
            $this->_mapping = array_merge_recursive($this->_mapping, $this->_parse($db, $params));
        }
    }

    /**
     * @param $className
     * @return Zend_Db_Adapter_Abstract
     */
    public function getConnection($className) {
        if (is_array($this->_mapping)) {
            if (isset($this->_mapping[self::TYPE_SIMPLE][$className])) {
                return $this->getDbConnection($this->_mapping[self::TYPE_SIMPLE][$className]);
            }
            foreach ($this->_mapping[self::TYPE_PREFIX] as $prefix => $db) {
                if (strpos($className, $prefix) !== false) {
                    return $this->getDbConnection($db);
                }
            }
        }
        return $this->_getPrimaryDbConnection();
    }

    /**
     * @param $db
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbConnection($db) {
        if ($db == self::PRIMARY_DB) {
            return $this->_getPrimaryDbConnection();
        } else {
            return $this->_geSecondaryDbConnection($db);
        }
    }

    private function _geSecondaryDbConnection($db) {
        if (!isset($this->_connections[$db])) {
            $cfg = Zend_Registry::get('cfg');
            if (isset($cfg['entity-db'][$db])) {
                $settings = $cfg['entity-db'][$db];
                $connection = Zend_Db::factory($settings['adapter'], $settings['params']);
                $connection->setFetchMode(Zend_Db::FETCH_OBJ);
                $this->_connections[$db] = $connection;
            } else {
                throw new Exception("Wrong db type was given. $db doesn't exist");
            }
        }
        return $this->_connections[$db];
    }

    private function _getPrimaryDbConnection() {
        return Zend_Registry::get('db');
    }

    private function _parse($db, array $params) {
        $result = array(
            self::TYPE_SIMPLE => array(),
            self::TYPE_PREFIX => array()
        );
        foreach ($params as $param) {
            $type = $this->_isPrefix($param) ? self::TYPE_PREFIX : self::TYPE_SIMPLE;
            $result[$type][$param] = $db;
        }
        return $result;
    }

    /**
     * simple check
     * @param $param
     * @return bool
     */
    private function _isPrefix($param) {
        return $param[strlen($param) - 1] == '_';
    }

}