<?php
class RM_Photo_Resizer {

    /**
     * @var RM_Photo
     */
    protected $_photo;

    public function __construct(RM_Photo $photo) {
        $this->_photo = $photo;
    }

    public function getPath($width = null, $height = null) {
        if (is_null($width) && is_null($height)) { # original
            return $this->getSavePath();
        } else {
            if (is_null($width) && $this->_photo->getHeight() !== 0) {
                $width = $height / $this->_photo->getHeight() * $this->_photo->getWidth();
            }
            if (is_null($height) && $this->_photo->getWidth() !== 0) {
                $height = $width / $this->_photo->getWidth() * $this->_photo->getHeight();
            }
            return $this->_getResizedPath($width, $height);
        }
    }

    public function getProportionalPhoto($maxWidth, $maxHeight, &$width = null, &$height = null) {
        $height = $this->_photo->getHeight();
        $width = $this->_photo->getWidth();
        if ($height == 0 || $width == 0) {
            return '';
        }
        if ($maxWidth / $width < $maxHeight / $height) {
            $height = floor($maxWidth / $width * $height);
            $width = $maxWidth;
        } else {
            $width = floor($maxHeight / $height * $width);
            $height = $maxHeight;
        }
        return $this->_getResizedPath($width, $height);
    }

    public function getFullPhotoPath() {
        return PUBLIC_PATH . $this->getSavePath();
    }

    public function getSavePath() {
        return $this->_photo->_getSavePath();
    }

    public function removeOldPhotos() {

    }

    protected function _getResizedPath($width, $height) {
        return self::getProportionPath($width, $height, $this->getSavePath());
    }

    protected static function getProportionPath($width, $height, $imagePath) {
        return join('', array(
            '/image.php?',
            "width={$width}&",
            "height={$height}&",
            "crop&",
            "image={$imagePath}"
        ));
    }

}