<?php
class RM_System_Browser {

    private $_curl;

    /**
     * @var RM_System_Browser_DownloadStrategy
     */
    private $_downloadStrategy;

    public function __construct() {
        $this->__initCurl();
        $this->__initCurlParams();
    }

    public function setMaxWaiting($seconds) {
        curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $seconds);
    }

    public function likeBrowser() {
        curl_setopt($this->_curl, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)");
        curl_setopt($this->_curl, CURLOPT_REFERER, "http://google.com");
    }
    
    public function setPostData(array $data) {
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
    }
    
    public function setRemoteIP($ip) {
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array(
            "REMOTE_ADDR: $ip",
            "HTTP_X_FORWARDED_FOR: $ip"
        ));
    }
    
    /**
     * @param RM_System_Browser_DownloadStrategy $downloadStrategy
     */
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
            return $this->getDownloadStrategy()->download( $this->_curl, $url );
        } else {
            throw new Exception('Wrong url given');
        }
    }
    
    protected function __initCurl() {
        $this->_curl = curl_init();
    }
    
    protected function __initCurlParams() {
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->_curl, CURLOPT_VERBOSE, false);
        curl_setopt($this->_curl, CURLOPT_HEADER, false);
        curl_setopt($this->_curl, CURLOPT_NOSIGNAL, false);
        curl_setopt($this->_curl, CURLINFO_HEADER_OUT, false);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->_curl, CURLOPT_NOPROGRESS, true);
        curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
    }

}