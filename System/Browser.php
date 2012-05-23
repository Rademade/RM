<?php
class RM_System_Browser {

	private $_curl;

    /**
     * @var RM_System_Browser_DownloadStrategy
     */
    private $_downloadStrategy;

	public function __construct() {
		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->_curl, CURLOPT_VERBOSE, false);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_MUTE, true);
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

    public function setDownloadStrategy(RM_System_Browser_DownloadStrategy $downloadStrategy) {
        $this->_downloadStrategy = $downloadStrategy;
    }

    /**
     * @return RM_System_Browser_DownloadStrategy
     */
    public function getDownloadStrategy() {
        if (!$this->_downloadStrategy instanceof RM_System_Browser_DownloadStrategy) {
            $this->_downloadStrategy = new RM_System_Browser_DownloadStrategy_Simple();
        }
        return $this->_downloadStrategy;
    }

	public function download($url) {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->getDownloadStrategy()->download(
                $this->_curl,
                $url
            );
        } else {
            throw new Exception('Wrong url given');
        }
    }
	
}