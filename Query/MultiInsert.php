<?php
class RM_Query_MultiInsert {

	private $tableName;
	private $fields = array();
	private $data = array();
	private $query = '';
	
	public function __construct($tableName) {
		$this->tableName = trim(htmlspecialchars($tableName));
		if ($this->tableName === '') {
			throw new Exception('TABLE NAME NOT GIVEN');
		}
	}
	
	public function addData(array $data) {
		if (empty($this->fields)) {
			foreach ($data as $key => $value) {
				$this->fields[] = htmlspecialchars($key);
			}
		}
		if (count($data) !== count($this->fields)) {
			throw new Exception('WRONG DATA GIVEN');
		}
		$i = 0;
		foreach ($data as $key => $value) {
			if (!isset($this->fields[$i]) || $key !== $this->fields[$i]) {
				throw new Exception('WRONG KEYS GIVEN');
			}
			$i++;
		}
		$this->data[] = $data;
	}
	
	public function execute() {
		if (empty($this->data))
			return ;
		$this->query = 'INSERT INTO ' . $this->tableName . ' (' . join(',', $this->fields) . ') VALUES';
		$i = 0;
		$dataCount = count($this->data);
		$insertPreparedData = array();
		foreach ($this->data as $insertData) {
			++$i;
			$this->query .= '(';
			$valsCount = count($insertData);
			$j = 0;
			foreach ($insertData as $val) {
				++$j;
				$this->query .= '?';
				$insertPreparedData[] = $val;
				if ($j !== $valsCount) {
					$this->query .= ', ';
				}
			}
			$this->query .= ')';
			if ($dataCount !== $i) {
				$this->query .= ', ';
			}
		}
		$db = Zend_Registry::get('db');
		$db->query($this->query, $insertPreparedData);
	}
	
}