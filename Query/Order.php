<?php
class RM_Query_Order
    implements
        RM_Query_Interface_ImproveSelect,
        RM_Query_Interface_Hashable {
    
    private $_orders = array();
    private $_expr;
    private $_isRandom = false;

    const ASC = 1;
    const DESC = 2;

    /**
     * @static
     * @return RM_Query_Order
     */
    public static function get() {
        return new self();
    }

    /**
     * @param string $field
     * @param int $type
     * @return RM_Query_Order
     */
    public function add($field, $type) {
        //TODO check for duplicate
        $this->_orders[] = (object)array(
            'field' =>  $this->_checkField($field),
            'type'  =>  $this->_checkType($type)
        );
        return $this;
    }

    /**
     * @deprecated
     * @param $field
     * @param $type
     * @return RM_Query_Order
     */
    public function addOrder($field, $type) {
        $this->add($field, $type);
        return $this;
    }

    /**
     * @param Zend_Db_Select $select
     */
    public function improveQuery(Zend_Db_Select $select) {
        $execOrder = array();
        if ($this->_expr instanceof Zend_Db_Expr) {
            $select->order( $this->_expr );
        } else {
            foreach ($this->_orders as $order) {
                $execOrder[] = join(' ', array(
                    $order->field,
                    $this->_getType( $order->type )
                ));
            }
            $select->order( $execOrder );
        }
    }

    /**
     * @param RM_Query_Order $order
     */
    public function mergeWith(self $order) {
        if ($order->_expr instanceof Zend_Db_Expr) {
            $this->_expr = $order->_expr;
        }
        foreach ($order->_orders as $orderData) {
            $this->add(
                $orderData->field,
                $orderData->type
            );
        }
    }

    public function isReady() {
        return (!empty($this->_orders));
    }

    public function byRandom() {
        $this->_isRandom = true;
        $this->_expr = new Zend_Db_Expr('RAND()');
    }

    public function isRandom() {
        return $this->_isRandom;
    }

    public function isHashable(){
        if ($this->isRandom()) {
            return false;
        } else {
            return true;
        }
    }

    public function getHash() {
        $orders = '';
        if (!$this->isRandom()) {
            $orders = join(',', $this->toArray());
        }
        return '_' . md5($orders);
    }

    public function toArray() {
        $orders = [];
        foreach ($this->_orders as $order) {
            $orders[] = $order->field . '.' . $this->_getType($order->type);
        }
        return $orders;
    }


    private function _checkField($name) {
        $name = trim($name);
        if ($name === '') {
            throw new Exception('WRONG FIELD GIVEN');
        }
        return $name;
    }
    
    private function _checkType($type) {
        switch ($type) {
            case 'ASC':
            case self::ASC:
                return self::ASC;
            case 'DESC':
            case self::DESC:
                return self::DESC;
            default:
                throw new Exception('Wrong order type given');
        }
    }

    private function _getType($type) {
        switch (intval($type)) {
            case self::ASC:
                return 'ASC';
                break;
            case self::DESC:
                return 'DESC';
                break;
            default:
                throw new Exception('Wrong order type given');
        }
    }


}