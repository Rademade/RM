<?php
class RM_Photo_Resize_Resizer {

    protected $_imagePath;

    /**
     * @var Imagick
     */
    protected $_imagick;

    public function __construct($fullImagePath) {
        $this->_imagePath = $fullImagePath;
    }

    public function cropImage($width, $height) {
        $this->_getImagick()->cropthumbnailimage($width, $height);
        return $this;
    }

    public function unSharpedCrop($width, $height) {
        $originWidth = $this->_getImagick()->getImageWidth();
        $originHeight = $this->_getImagick()->getImageHeight();
        $originProportion = $originWidth / $originHeight;
        $resizeProportion = $width / $height;

        if ($originProportion != $resizeProportion) {
            if ($originProportion > 1) {
                $cropHeight = $originHeight;
                $cropWidth = $cropHeight * $resizeProportion;
            } else {
                $cropWidth = $originWidth;
                $cropHeight = $cropWidth / $resizeProportion;
            }
            $x = ($originWidth - $cropWidth) / 2;
            $y = ($originHeight - $cropHeight) / 2;
            $this->_getImagick()->cropImage($cropWidth, $cropHeight, $x, $y);
        }
        $this->_getImagick()->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
        return $this;
    }

    public function resizeImage($width, $height) {
        $this->_getImagick()->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
        return $this;
    }

    public function saveImage($savePath) {
//        $this->_getImagick()->setImageFormat('webp');
        $this->_getImagick()->writeImage( $savePath );
        $this->_getImagick()->clear();
        $this->_getImagick()->destroy();
    }

    protected function _getImagick() {
        if (!($this->_imagick instanceof Imagick)) {
            $this->_imagick = new Imagick( $this->_imagePath );
        }
        return $this->_imagick;
    }

}