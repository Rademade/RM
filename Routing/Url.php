<?php
class RM_Routing_Url {

	private $url;

	/**
	 * @access public
	 * @param string url
	 * @ParamType url string
	 */
	public function __construct($url) {
		$this->url = $url;
	}
	
	public function format() {
		$this->url = trim(mb_strtolower($this->url, 'UTF-8'));
		$this->url = str_replace(' ', '-', $this->url);
		$this->url = str_replace(array(
			"'", '"', '&', ',', '.', '?', '+', '!', '(', ')', '»', '«',
			'$', "\\", ';', '[', ']', '—', '#', '=', '↑', '*',
			'…', '%', '{', '}', '”', ';'
		), '', $this->url);
		$this->url = $this->stripLastSlashes($this->url);
		$translit = new RM_Routing_Url_Translite($this->url);
		$this->url = $translit->__toString();
        return $this;
	}

	public function checkUnique($excludedId = null) {
		$db = Zend_Registry::get('db');
		/* @var $db Zend_Db_Adapter_Abstract*/
		$select = $db->select();
		/* @var $select Zend_Db_Select */
		$select->from('routing',array(
			'count'=>'COUNT(idRoute)'
		))->where('url = ? ',$this->url);
		if (!is_null($excludedId)) {
			$select->where('idRoute != ? ',$excludedId);
		}
		$select->where('routeStatus != ? ', RM_Interface_Deletable::STATUS_DELETED);
		$res = $db->fetchRow($select);
		return intval($res->count) === 0;
	}

	public function stripLastSlashes($url) {
    	return rtrim($url, "/");
	}
	
	public function checkFormat(array $params ) {
		$url = $this->url;
		foreach ($params as  $param => $value) {
			$url = str_replace('/:' . $param, '', $url);
		}
		return preg_match('/^\/[a-z0-9\_\&\.\-\/]*\/?$/i', $url);
	}

	public function getInitialUrl() {
		return $this->url;
	}

	public function getAssembledUrl( array $params ) {
		$url = $this->url;
		foreach ($params as  $param => $value) {
			$url = str_replace(':' . $param, $value, $url);
		}
		return $url;
	}

}
