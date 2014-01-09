<?php
class RM_System_Cache {
	
	private $cfg = null;
	private $isCached = false;
	private $baseCM;
	
	public function __construct($additionalPrefix = '') {
		$cfg = Zend_Registry::get('cfg');
		$this->cfg = (object)$cfg['cache'];
        $this->cfg->prefix .= $additionalPrefix;
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

    private function _getOptions() {
        return new Zend_Config( array(), true );
    }

    /**
     * @param $config
     * @param $key
     * @return array
     */
    private function _mergeOptions($config, $key) {
        /* @var Zend_Config $config*/
        $config = isset($config->{$key}) ? $config->{$key} : self::_getOptions();
        $data = array_merge(
            $this->cfg->front,
            $config->toArray()
        );
        return $data;
    }

    private function _setBaseOptions($options, $key) {
        if (!isset($options->{$key}))
            $options->{$key} = self::_getOptions();
        if (!isset($options->{$key}->lifetime) || !$options->{$key}->lifetime)
            $options->{$key}->lifetime = NULL;
        return $options;
    }

	private function getFrontOptions($options) {
        $frontConfig = self::_mergeOptions($options, 'front');
        $frontConfig['caching'] = $this->isCached;
        $frontConfig['cache_id_prefix'] = $this->cfg->prefix . $options->cacheName;
		return $frontConfig;
	}
	
	private function getBackOptions($options) {
        $backConfig = self::_mergeOptions($options, 'back');
        $backConfig['cache_id_prefix'] = $this->cfg->prefix;
        return $backConfig;
	}
	
	private function getFront($options) {
		return array(
			'name' => !is_null($options->type) ? $options->type :'Core' ,
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
        $this->_setBaseOptions($options, 'front');
        $this->_setBaseOptions($options, 'back');
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