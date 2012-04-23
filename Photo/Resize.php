<?php
class Resize {

	private $_dirPath;
	private $_imagePath;
	private $_fullPath;

	private $_width;
	private $_height;
	private $_crop;

	private $_hashDir;

	/**
	 * @var Imagick
	 */
	private $_imagick;
	/**
	 * @var array
	 */
	private $_size;

	const SAVE_PATH = '/imagecache/';

	public function __construct($dirPath, $path) {
		$this->_dirPath = $dirPath;
		$this->_imagePath =  $this->_dirPath . $path;
		if (!is_file($this->_imagePath)) {
			throw new Exception('Wrong image path given');
		} else {
			$this->_fullPath = $this->_imagePath;
			if (($this->_size = getimagesize( $this->_fullPath )) === false) {
				throw new Exception('Wrong image file given');
			}
		}
	}

	public function getWidth() {
		return !is_null( $this->_width ) ? $this->_width : $this->_size[0];
	}

	public function getHeight() {
		return !is_null( $this->_height ) ? $this->_height : $this->_size[1];
	}

	public function isCrop() {
		return $this->_crop;
	}

	public function getMime() {
		return $this->_size['mime'];
	}

	public function getImagick() {
		if (!($this->_imagick instanceof Imagick)) {
			$this->_imagick = new Imagick( $this->_fullPath );
		}
		return $this->_imagick;
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

	public function getHash() {
		return join('/', array(
            $this->_getHashDir(),
		    $this->_getHashName()
		));
	}

	public function resize($width, $height, $crop = false) {
		$this->_width = $width;
		$this->_height = $height;
		$this->_crop = ($crop) ? true : false;
		if (!is_file( $this->_getThumbPath() )) {
			$this->_createImage();
		}
	}

	public function echoImage() {
		$this->_cacheHeaders();
		header('Content-Type: ' . $this->getMime());
		header("Content-Length: " . filesize( $this->_getThumbPath()) );
		echo file_get_contents( $this->_getThumbPath() );
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
			$this->_hashDir = $this->_implodeHash( md5( $this->_imagePath ) );
		}
		return $this->_hashDir;
	}

	private function _getThumbPath() {
		return join('', array(
            $this->_dirPath,
            self::SAVE_PATH,
            $this->getHash()
        ));
	}

	private function _getThumbPathDir() {
		return join('', array(
            $this->_dirPath,
            self::SAVE_PATH,
            $this->_getHashDir()
        ));
	}

	private function _resizeImage() {
		//TODO test simple resize
		$this->getImagick()->resizeImage(
			$this->getWidth(),
			$this->getHeight(),
			Imagick::FILTER_LANCZOS,
			1
		);
	}

	private function _cropImage(){
		//TODO crop without thumbnail
		$this->getImagick()->cropThumbnailImage(
			$this->getWidth(),
			$this->getHeight()
		);
	}

	private function _createImage() {
		($this->isCrop()) ? $this->_cropImage() : $this->_resizeImage();
		if (!file_exists($this->_getThumbPathDir())) {
			mkdir($this->_getThumbPathDir(), 0777, true);
		}
		$this->getImagick()->writeImage( $this->_getThumbPath() );
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
		header('Cache-Control: max-age=' . 60*60*24*365);
	}

}