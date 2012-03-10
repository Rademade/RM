<?php
class RM_System_Browser {

	private $_curl;
	
	public function __construct() {
		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->_curl, CURLOPT_VERBOSE, true);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
	}
	
	public function likeBrowser() {
		curl_setopt($this->_curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
		curl_setopt($this->_curl, CURLOPT_REFERER, "http://google.com");
	}
	
	public function setRemoteIP($ip) {
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array(
			"REMOTE_ADDR: $ip",
			"HTTP_X_FORWARDED_FOR: $ip"
		));
	}
	
	public function _getProxyUrl($url) {
		return 'http://proxy2974.my-addr.net/myaddrproxy.php/' . str_replace('://', '/', $url);
	}
	
	public function download($url, $proxy = false) {
		if ($proxy) {
			$url = $this->_getProxyUrl( $url );
		}
		$curl = curl_copy_handle( $this->_curl );
		curl_setopt($curl, CURLOPT_URL, $url);
		$buffer = curl_exec($curl);
		curl_close($curl);
		return $buffer;
	}
	
}