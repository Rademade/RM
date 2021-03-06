<?php
class RM_Entity_Search_Entity
    extends
        RM_Entity_Search_Abstract_Abstract {

    /**
     * @var RM_Entity
     */
    protected $_entityName = '';

    public function __construct($entityClassName) {
        $this->_setEntityName( $entityClassName );
    }

    /**
     * @param string $entityName
     * @throws Exception
     */
    private function _setEntityName($entityName) {
        $reflection = new ReflectionClass( $entityName );
        if ($reflection->isSubclassOf('RM_Entity')) {
            $this->_entityName = $entityName;
        } else {
            throw new Exception('To _setEntityName() must be given class name instanceof RM_Entity');
        }
    }

    /**
     * @return RM_Entity[]|RM_Entity_Search_Result_Interface[]
     */
    public function getResults() {
        return call_user_func_array( array(
            $this->_entityName,
            '_initList'
        ), array(
            $this->__getSelect(),
            func_get_args()
        ) );
    }

    /**
     * @return RM_Entity
     */
    public function getFirst() {
        return call_user_func_array( array(
            $this->_entityName,
            '_initItem'
        ), array(
            $this->__getSelect(),
            func_get_args()
        ) );
    }

    public function getCount() {
        $model = $this->_entityName;
        return RM_Query_Exec::getRowCount(
            $this->__getSelect(),
            'DISTINCT(' . $this->_getKeyFieldName($model) . ')'
        );
    }

    /**
     * @return Zend_Db_Select
     */
    public function __getSelect() {
        $select = call_user_func( array(
            $this->_entityName,
            '_getSelect'
        ) );
        $this->__installQueryCondition( $select );
        return $select;
    }

    /**
     * @param RM_Entity $model
     * @return string
     */
    private function _getKeyFieldName($model) {
        return join('.', array(
            $model::TABLE_NAME,
            $model::getKeyAttributeField()
        ));
    }

}