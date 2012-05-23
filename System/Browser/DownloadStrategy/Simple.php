<?php
class RM_System_Browser_DownloadStrategy_Simple
    implements
        RM_System_Browser_DownloadStrategy {


    public function download($curl, $url) {
        curl_setopt($curl, CURLOPT_URL, $url);
        $html = curl_exec($curl);
        curl_close($curl);
        return $html;
    }

}