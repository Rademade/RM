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

    private function _getAllStyles() {
        $css = '';
        foreach ($this->_src as $path) {
            $css .= file_get_contents($path);
        }
        $css = "/* Compress */\n" . $css;
        return preg_replace('/(\@?charset \"?utf-8\"?\;?)/i','', $css);
    }

    private function _getCurl() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, []);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 7);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, 'http://compressor.rademade.com/css');
        curl_setopt($curl, CURLOPT_POST, true);
        return $curl;
    }

    private function _getCompressedCss() {
        $curl = $this->_getCurl();
        curl_setopt($curl, CURLOPT_POSTFIELDS, array(
            'css' => $this->_getAllStyles()
        ));
        return curl_exec($curl);
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