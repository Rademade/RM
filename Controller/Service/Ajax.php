<?php
class RM_Controller_Service_Ajax {

    const RESPONSE_STATUS_FAIL = 0;
    const RESPONSE_STATUS_OK = 1;

    protected static $__typeToMethod = array(
        RM_Interface_Sortable::ACTION_SORT => 'sort',
        RM_Interface_Element::ACTION_POSITION => 'position',
        RM_Interface_Hideable::STATUS_SHOW => 'changeHideStatus',
        RM_Interface_Hideable::STATUS_HIDE => 'changeHideStatus',
        RM_Interface_Deletable::ACTION_DELETE => 'delete',
    );

    /**
     * @var RM_Entity
     */
    protected $_entityClassName;

    public function __construct($entityClassName) {
        $this->_entityClassName = $entityClassName;
    }

    public function processRequest(stdClass $data) {
        $result = 'Type of AJAX process not given';
        if (isset($data->type))  {
            $method = $this->__typeToMethod( (int)$data->type );
            if (is_string($method)) {
                $result = $this->{$method}($data);
            }
        }
        return $result;
    }

    public function sort(stdClass $data) {
        $status = self::RESPONSE_STATUS_FAIL;
        if (isset($data->ids)) foreach ($data->ids as $position => $id) {
            /* @var stdClass $positionData*/
            $positionData = (object)['id' => $id, 'position' => $position];
            $this->position($positionData);
        }
        return ['status' => $status];
    }

    public function position(stdClass $data) {
        return $this->__itemProcessWrapper($data, 'RM_Interface_Sortable', function ($item) use ($data) {
            /* @var RM_Interface_Sortable $item */
            $item->setPosition($data->position);
            /* @var RM_Entity $item */
            $item->save();
        });
    }

    public function changeHideStatus(stdClass $data) {
        return $this->__itemProcessWrapper($data, 'RM_Interface_Hideable', function ($item) {
            /* @var RM_Interface_Hideable $item */
            ($item->isShow()) ? $item->hide() : $item->show();
        });
    }

    public function delete(stdClass $data) {
        return $this->__itemProcessWrapper($data, 'RM_Interface_Deletable', function($item) {
            /* @var RM_Interface_Deletable $item */
            $item->remove();
        });
    }

    protected function __itemProcessWrapper(stdClass $data, $instanceName, Closure $itemProcess) {
        $status = self::RESPONSE_STATUS_FAIL;
        if (isset($data->id)) {
            $item = $this->_getItemById($data->id);
            if (is_a($item, $instanceName)) {
                call_user_func($itemProcess, $item);
                $status = self::RESPONSE_STATUS_OK;
            }
        }
        return ['status' => $status];
    }

    /**
     * @param  int $id
     * @return RM_Entity
     */
    protected function _getItemById($id) {
        return call_user_func([$this->_entityClassName, 'getById'], (int)$id);
    }

    protected function __typeToMethod($type) {
        return isset( self::$__typeToMethod[$type] ) ? self::$__typeToMethod[$type] : null;
    }

}