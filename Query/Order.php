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
	
	public function addOrder($field, $type) {
		$this->_orders[] = array(
			$this->checkField($field),
			$this->checkType($type)
		);
	}
	
	private function checkField($name) {
		$name = trim($name);
		if ($name === '') {
			throw new Exception('WRONG FIELD GIVEN');
		}
		return $name;
	}
	
	private function checkType($type) {
		$type = (int)$type;
		if (in_array($type, array(
			self::ASC,
			self::DESC
		))) {
			return $type;
		} else {
			throw new Exception('WRONG TYPE GIVEN');
		}
	}
	
	public function byRandom() {
		$this->_isRandom = true;
		$this->_expr = new Zend_Db_Expr('RAND()');
	}
	
	private function getType($type) {
		switch (intval($type)) {
			case self::ASC:
				return 'ASC';
				break;
			case self::DESC:
				return 'DESC';
				break;
		}
	}
	
	public function isReady() {
		return (!empty($this->_orders));
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
		$key = '';
		if ($this->isRandom()) {
			$key = 'RAND_' . rand(0, 15);
		} else {
			foreach ($this->_orders as $order) {
				$key = (',' . join('+', $order));
			}
		}
		return '_' . md5($key);
	}
	
	
	public function improveQuery(Zend_Db_Select $select) {
		$execOrder = array();
		if ($this->_expr instanceof Zend_Db_Expr) {
			$select->order( $this->_expr );
		} else {
			foreach ($this->_orders as $order) {
				$execOrder[] = $order[0] . ' ' . $this->getType( $order[1] );
			}
			$select->order( $execOrder );
		}
	}
	
}