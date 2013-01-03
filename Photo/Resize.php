<?php
class Resize {

	private $_rootDirPath;
	private $_rootImagePath;

	private $_width;
	private $_height;
	private $_crop;
    private $_thumbPath;

	private $_hashDir;

	/**
	 * @var Imagick
	 */
	private $_imagick;
	/**
	 * @var array
	 */
	private $_size;

	const DEFAULT_CACHE_PATH = '/imagecache/';

	public function __construct($rootDirPath, $imagePath) {
		$this->_rootDirPath = $rootDirPath;
        $urlParams = parse_url($imagePath);
		$this->_rootImagePath =  $this->_rootDirPath . $urlParams['path'];
		if (!is_file($this->_rootImagePath)) {
			throw new Exception('Wrong image path given');
		} else {
			if ($this->getSize() === false) {
				throw new Exception('Wrong image file given');
			}
		}
	}

	public function getWidth() {
		return !is_null( $this->_width ) ? $this->_width : $this->getSize()[0];
	}

	public function getHeight() {
		return !is_null( $this->_height ) ? $this->_height : $this->getSize()[1];
	}

	public function isCrop() {
		return $this->_crop;
	}

	public function getMime() {
		return $this->getSize()['mime'];
	}

	public function getImagick() {
		if (!($this->_imagick instanceof Imagick)) {
			$this->_imagick = new Imagick( $this->_rootImagePath );
		}
		return $this->_imagick;
	}

    public function getSize() {
        if (!is_array($this->_size)) {
            $this->_size = getimagesize( $this->_rootImagePath );
        }
        return $this->_size;
    }

    public function writeImage($savePath, $width, $height, $crop = false) {
        if (!(is_null($width) && is_null($height))) {
            if (is_null($width) && $this->getHeight() !== 0) {
                $width = $height / $this->getHeight() * $this->getWidth();
            }
            if (is_null($height) && $this->getWidth() !== 0) {
                $height = $width / $this->getWidth() * $this->getHeight();
            }
        }
        $this->_width = $width;
        $this->_height = $height;
        $this->_crop = $crop;
        $this->_thumbPath = $savePath;
        $this->_createImage();
    }

    /**
     * Create thumb and save image cache file
     *
     * @param $width
     * @param $height
     * @param bool $crop
     */
    public function resize($width, $height, $crop = false) {
		$this->_width = $width;
		$this->_height = $height;
		$this->_crop = $crop;
		if (!is_file( $this->_getRootThumbPath() )) {//if null -> generates unique path
            $this->_createDir();
			$this->_createImage();
		}
	}

    public function echoImage() {
        //TODO check if thumb created
		$this->_cacheHeaders();
		header('Content-Type: ' . $this->getMime());
		header("Content-Length: " . filesize( $this->_getRootThumbPath()));
		echo file_get_contents($this->_getRootThumbPath());
	}


    private function _getThumbName() {
        return join('/', array(
            $this->_getHashDir(),
            $this->_getHashName()
        ));
    }

    private function _getRootThumbPath() {
        if (!$this->_thumbPath) {
            $this->_thumbPath = join('', array(
                $this->_rootDirPath,
                self::DEFAULT_CACHE_PATH,
                $this->_getThumbName()
            ));
        }
        return $this->_thumbPath;
    }

	private function _getHashName() {
		return join('.', array(
			$this->getWidth(),
			$this->getHeight(),
			$this->isCrop()
       ));
	}

	private function _getHashDir() {
		if (is_null( $this->_hashDir )) {
			$this->_hashDir = $this->_implodeHash( md5( $this->_rootImagePath ) );
		}
		return $this->_hashDir;
	}

	private function _resizeImage() {
		$this->getImagick()->resizeImage(
			$this->getWidth(),
			$this->getHeight(),
			Imagick::FILTER_LANCZOS,
			1
		);
	}

	private function _cropImage(){
		$this->getImagick()->cropThumbnailImage(
			$this->getWidth(),
			$this->getHeight()
		);
	}

    private function _implodeHash( $hash ) {
        $i = 0;
        $stepLength = 11;
        $implodedHash = '';
        while ($i < strlen($hash) - $stepLength) {
            $segment = substr($hash, $i, $stepLength);
            $implodedHash .= $segment . '/';
            $i += $stepLength;
        }
        return $implodedHash . substr($hash, $i, $stepLength);
    }

	private function _createDir() {
		$dir = join('', array(
            $this->_rootDirPath,
            self::DEFAULT_CACHE_PATH
        ));
		foreach (explode('/', $this->_getHashDir()) as $segment) {
			$dir .= ($segment . '/');
			if (!is_dir($dir)) {
				mkdir($dir, 0777);
			}
		}
	}

	private function _createImage() {
		($this->isCrop()) ? $this->_cropImage() : $this->_resizeImage();
		$this->getImagick()->writeImage($this->_getRootThumbPath());
		$this->getImagick()->clear();
		$this->getImagick()->destroy();
	}

	private function _cacheHeaders() {
		header( join(' ', array(
			'Expires:' .
			gmdate('D, d M'),
			(gmdate('Y') + 1),
			gmdate('H:i:s'),
			'GMT'
        )));
		header('Pragma: cache');
		header('Cache-Control: max-age=' . 60 * 60 * 24 * 365);
	}

}