<?php
class RM_System_Browser_DownloadStrategy_MegabyteProxy
    implements
        RM_System_Browser_DownloadStrategy {

    const API_KEY = 'db3igejkov31u9hepcw;';

    private function _getProxyUrl($url) {
        return join('', array(
            'http://megabyte.net.ua/proxy.php?',
            join('&', array(
                'API_KEY=' . self::API_KEY,
                'url=' . base64_encode($url)
            ))
        ));
    }

    public function download($curl, $url) {
        curl_setopt($curl, CURLOPT_URL, $this->_getProxyUrl( $url ));
        $html = curl_exec($curl);
        curl_close($curl);
        return $html;
    }

}