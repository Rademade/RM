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
	}

	public function checkUnique($excludedId = null) {
		$db = Zend_Registry::get('db');
		$select = $db->select()
			->from('routing',array(
				'count'=>'COUNT(idRoute)'
			))
			->where('url = ? ',$this->url);
		if (!is_null($excludedId)) {
			$select->where('idRoute != ? ',$excludedId);
		}
		$res = $db->fetchRow($select);
		return intval($res->count) === 0;
	}

	public function stripLastSlashes($url) {
    	if (substr($url, -1) === '/' && $url !== '/') {
    		$url = substr($url, 0, -1);
    		return (substr($url, -1) === '/') ? 
    			$this->stripLastSlashes($url) :
    			$url;
    	} else {
    		return $url;
    	}
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
