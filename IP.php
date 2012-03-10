<?php 
class RM_IP {
	
	private $ip;
	private $data = array();

	const CACHE = 'IP';
	
	public function __construct( $ip ) {
		$this->ip = $ip;
	}
	
	private function getData() {
		if (empty($this->data)) {
			if ( ($this->data = $this->load()) === false ) {
				$content = @file_get_contents('http://api.hostip.info/?ip=' . $this->ip);
				if ($content) {
					$xml = new SimpleXmlElement($content);
					$hostIp = $xml->children('gml', true)->featureMember->children('', true)->Hostip;
					$this->data = (object)array(
						'citystate' => $hostIp->children('gml', TRUE)->name->__toString(),
						'country' => $hostIp->countryName->__toString()
					);
				} else {
					$this->data = (object)array(
						'citystate' => '',
						'country' => ''
					);
				}
				$this->cache( $this->data );
			}
		}
		return $this->data;
	}
	
	private function getIpCache() {
		return md5(ip2long( $this->ip ));
	}

	private function load() {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache( self::CACHE );
		return $cache->load( $this->getIpCache() );
	}
	
	private function cache( $data ) {
		$cachemanager = Zend_Registry::get('cachemanager');
		$cache = $cachemanager->getCache( self::CACHE );
		$cache->save($data, $this->getIpCache() );
	}

	public function getCountryName() {
		return $this->getData()->country;
	}
	
	public function getCity() {
		return $this->getData()->citystate;
	}

}