<?php
require_once 'Resize/PathLoader.php';
require_once 'Resize/Resizer.php';
require_once 'Resize/ResizedImage.php';

class RM_Resize extends Resize {

}

class Resize {

    const RESOLUTION_MAX_WIDTH = 3000;
    const RESOLUTION_MAX_HEIGHT = 2000;

    /**
     * @var RM_Photo_Resize_PathLoader
     */
    protected $_pathLoader;

    /**
     * @var RM_Photo_Resize_Resizer
     */
    protected $_resizer;

    protected $_rootDirPath;
    protected $_imagePath;
    protected $_resizedImagePath;

	/**
	 * @var Imagick
	 */
    protected $_imagick;
	/**
	 * @var array
	 */
    protected $_size;

    /**
     * TODO remove arguments. Need only for path loader
     * @param $rootDirPath
     * @param $imagePath
     */
    public function __construct($rootDirPath, $imagePath) {
        $this->_rootDirPath = $rootDirPath;
        $this->_imagePath = parse_url($imagePath)['path'];
	}

    public function setPathLoader(RM_Photo_Resize_PathLoader $pathLoader) {
        $this->_pathLoader = $pathLoader;
    }

    public function getPathLoader() {
        if (!$this->_pathLoader instanceof RM_Photo_Resize_PathLoader) {
            $this->_pathLoader = new RM_Photo_Resize_PathLoader($this->_rootDirPath, $this->_imagePath);
        }
        return $this->_pathLoader;
    }

    public function setResizer(RM_Photo_Resize_Resizer $resizer) {
        $this->_resizer = $resizer;
    }

    public function getResizer() {
        if (!$this->_resizer instanceof RM_Photo_Resize_Resizer) {
            $this->_resizer = new RM_Photo_Resize_Resizer( $this->getPathLoader()->getOriginFullPath() );
        }
        return $this->_resizer;
    }

	public function getMime() {
		return $this->getSize()['mime'];
	}

    public function getSize() {
        if (is_null($this->_size)) {
            $this->_size = getimagesize( $this->getPathLoader()->getOriginFullPath() );
        }
        return $this->_size;
    }

    public function getOriginWidth() {
        return $this->getSize()[0];
    }

    public function getOriginHeight() {
        return $this->getSize()[1];
    }

    /**
     * Ресайз с выдерживанием пропорций
     *
     * @param $width
     * @param $height
     * @param bool $isCrop
     * @param int $maxWidth
     * @param int $maxHeight
     * @return RM_Photo_Resize_ResizedImage
     */
    public function proportionalResize($width, $height, $isCrop = false, $maxWidth = null, $maxHeight = null) {
        if ( !(is_null($width) && is_null($height)) ) {
            if (is_null($width)) {
                $width = $height / $this->getOriginHeight() * $this->getOriginWidth();
                $width = is_null($maxWidth) ? $width : min($width, $maxWidth);
            }
            if (is_null($height)) {
                $height = $width / $this->getOriginWidth() * $this->getOriginHeight();
                $height = is_null($maxHeight) ? $height : min($height, $maxHeight);
            }
        }
        return $this->resize($width, $height, $isCrop);
    }

    /**
     * Жесткий ресайз по заданым параметреам
     *
     * @param $width
     * @param $height
     * @param bool $isCrop
     * @return RM_Photo_Resize_ResizedImage
     */
    public function resize($width, $height, $isCrop = false) {
        $width = $this->_fixResizeWidth( $width );
        $height = $this->_fixResizeHeight( $height );
        $this->getPathLoader()->setResizeFileNameParams([$width, $height, $isCrop ? 1 : 0]);
        $resizedImagePath = $this->getPathLoader()->getFullResizedImagePath( );
		if ( !is_file( $resizedImagePath ) ) { //if null generates unique path
            $this->getPathLoader()->createDirForResizedImage();
            $this->_resizeProcessor( func_get_args() );//сделано для разширения метода и дополнительных аргуметов
            $this->getResizer()->saveImage( $resizedImagePath );
		}
        return new RM_Photo_Resize_ResizedImage($resizedImagePath, $this->getMime());
	}

    /**
     * Arguments from resize function
     * 0 - width
     * 1 - height
     * 2 - isCrop
     * @param $arguments
     */
    protected function _resizeProcessor($arguments) {
        $width = $arguments[0];
        $height = $arguments[1];
        $isCrop = $arguments[2];
        if ($isCrop) {
            $this->getResizer()->cropImage($width, $height);
        } else {
            $this->getResizer()->resizeImage($width, $height);
        }
    }

    protected function _fixResizeWidth($width = null) {
        return !is_null( $width ) ? min($width, self::RESOLUTION_MAX_WIDTH) : $this->getSize()[0];
    }

    protected function _fixResizeHeight($height = null) {
        return !is_null( $height ) ? min($height, self::RESOLUTION_MAX_HEIGHT) : $this->getSize()[1];
    }

}