<?php
class RM_Head_Compressor_CSS {

	private $_version = 1;
	private $_src = array();

	private $_cacheDir;

	public function add($link) {
		$this->_src[] = $link;
	}

	public function setCacheDir($cacheDir) {
		$this->_cacheDir = $cacheDir;
	}

	public function setVersion($ver) {
		$this->_version = (int)$ver;
	}

	public function getVersion() {
		return $this->_version;
	}

	private function _getParseStyles($css) {
		return preg_replace('/(\@?charset \"?utf-8\"?\;?)/i','', $css);
	}

	private function _getAllStyles() {
		$css = '';
		foreach ($this->_src as $path) {
			$css .= file_get_contents($path);
		}
        $css = "/* Compress */\n" . $css;
		return $this->_getParseStyles( $css );
	}

	private function _getCurl() {
		$curl = curl_init();
		$header = array();
		$header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/534.30 (KHApplication_Model_Kernel_Catalog_Good_ParserTML, like Gecko) Chrome/17.0.742.77 Safari/534.30');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 7);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_URL, 'http://refresh-sf.com/yui/');
		curl_setopt($curl, CURLOPT_POST, true);
		return $curl;
	}

	private function _getCompressedCss() {
		$curl = $this->_getCurl();
		curl_setopt($curl, CURLOPT_POSTFIELDS, array(
			'compresstext' => $this->_getAllStyles(),
			'type' => 'css'
		));
        $html = curl_exec($curl);
        $DomParser = RM_System_Parser_SimpleHtmlDom::strGetHtml($html);
        return $DomParser->find('textarea', 1)->innertext;
	}

	public function compress() {
		if (!is_file($this->_getCahceFileName())) {
			file_put_contents($this->_getCahceFileName(), $this->_getCompressedCss());
		}
	}

	private function _getHash() {
		return md5(join('.', $this->_src));
	}

	private function _getCahceFileName() {
		return $this->_cacheDir. $this->getFileName();
	}

	public function getFileName() {
		return $this->_getHash() . '_' . $this-> getVersion() . '.css';
	}

}