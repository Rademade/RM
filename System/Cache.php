<?php
class RM_System_Cache {
	
	private $cfg = null;
	private $isCached = false;
	private $baseCM;
	
	public function __construct() {
		$cfg = Zend_Registry::get('cfg');
		$this->cfg = (object)$cfg['cache'];
		$this->isCached = intval($this->cfg->enableCache) === 1;
		$this->baseCM = $this->createBaseCM();
	}

	private function createBaseCM() {
		if ($this->isCached) {
			$data = new stdClass();
			$data->cacheName = $this->cfg->cacheName;
			$this->prepareParams($data);
			return Zend_Cache::factory(
				'Core',
				$this->cfg->type,
				$this->getFrontOptions($data),
				$this->getBackOptions($data)
			);
		}
	}
	
	private function getFrontOptions($options) {
		$front = (array)(isset($options->front) ? $options->front : array());
		$front = array_merge(
			(array)$this->cfg->front,
			$front
		);
		$front['caching'] = $this->isCached;
		$front['cache_id_prefix'] = $this->cfg->prefix . $options->cacheName;
		return $front;
	}
	
	private function getBackOptions($options) {
		$back = (array)(isset($options->back) ? $options->back : array());
		$back = array_merge(
			(array)$this->cfg->back,
			$back
		);
		$back['cache_id_prefix'] = $this->cfg->prefix;
		return $back;
	}
	
	private function getFront($options) {
		return array(
			'name' => 'Core',
			'options' => $this->getFrontOptions($options)
		);
	}

	private function getBack($options) {
		return array(
			'name' => $this->cfg->type,
			'options' => $this->getBackOptions($options)
		);
	}
	
	private function prepareParams($options) {
		if (!isset($options->front)) {
			$options->front = new Zend_Config(array(), true);
		}
		if (!isset($options->back)) {
			$options->back = new Zend_Config(array(), true);
		}
		if (!isset($options->front->lifetime) || !$options->front->lifetime) {
			$options->front->lifetime = NULL;
		}
		if (!isset($options->back->lifetime) || !$options->back->lifetime) {
			$options->back->lifetime = NULL;
		}
	}
	
	private function _parse($fileName) {
		return new Zend_Config_Ini($fileName, null, array(
			'allowModifications' => true
		));
	}

	public function load() {
		if (($cm = $this->loadFromChache()) === false) {
			$cm = new Zend_Cache_Manager();
			$dir = $this->cfg->cacheIniDir;
			$handle = opendir($dir);
		 	while ( false !== ($file = readdir($handle)) ) {
		 		if (preg_match('/\.ini/', $file)) {
			 		foreach ( $this->_parse($dir . $file) as $routeName => $options ) {
			 			$this->prepareParams($options);
			 			$cm->setCacheTemplate(
							$routeName,
							array(
								'frontend'=> $this->getFront( $options ),
						   		'backend' => $this->getBack( $options )
							)
						);
			 		}
		 		}
		 	}
		}
		$this->saveCache($cm);
		return $cm;
	}
	
	public static function cleanAll() {
		$cache = new self();
		$cache->clear();
	}
	
	public function clear() {
		if ($this->isCached) {
			$this->baseCM->clean();
		}
	}

	private function loadFromChache() {
		if ($this->isCached) {
			return $this->baseCM->load($this->cfg->cacheName);
		}
		return false;
	}

	private function saveCache(Zend_Cache_Manager $cm) {
		if ($this->isCached) {
			$this->baseCM->save($cm, $this->cfg->cacheName);
		}
	}
	
}