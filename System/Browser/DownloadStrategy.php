<?php
interface RM_System_Browser_DownloadStrategy {

    /**
     * @abstract
     * @param $curl
     * @param $url
     * @return string
     */
    public function download($curl, $url);

}