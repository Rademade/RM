<?php
class RM_Photo_Resize_ResizedImage {

    private $_imagePath;
    private $_imageMime;

    public function __construct($resizedImagePath, $mime = null) {
        $this->_imagePath = $resizedImagePath;
        $this->_imageMime = $mime;
    }

    public function getMime() {
        //TODO if null load
        return $this->_imageMime;
    }

    public function echoImage() {
        if ( !is_file( $this->_imagePath ) ) {
            throw new Exception('Image thumb is not created');
        } else {
            $this->_setCacheHeaders();
            header('Content-Type: ' . $this->getMime());
            header("Content-Length: " . filesize( $this->_imagePath ));
            echo file_get_contents( $this->_imagePath );
        }
    }

    private function _setCacheHeaders() {
        header( join(' ', array(
            'Expires:',
            gmdate('D, d M'),
            (intval( gmdate('Y') ) + 1),
            gmdate('H:i:s'),
            'GMT'
        )));
        header('Pragma: cache');
        header('Cache-Control: max-age=' . 60 * 60 * 24 * 365);
    }

}