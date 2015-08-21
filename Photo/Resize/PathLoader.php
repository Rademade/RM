<?php

/**
 * Путь состоит из 4 сегментов
 * - root_dir
 * - cache_dir
 * - image_dir
 * - image_name
 *  В данном классе описана работа с этими сегментами
 * Class RM_Photo_Resize_PathLoader
 */
class RM_Photo_Resize_PathLoader {

    const DEFAULT_CACHE_PATH = '/imagecache/';

    protected $_rootDir;

    protected $_imageFullPath;

    protected $_resizeImageFileNameParams;

    protected $_resizedImageCacheDir;
    protected $_resizedImageDirPath;
    protected $_resizedImageFileName;

    public function __construct($rootDir, $imagePath) {
        $this->_rootDir = $rootDir;
        $this->_imageFullPath = $rootDir . $imagePath;
        $this->_checkImage();
    }

    public function getOriginFullPath() {
        return $this->_imageFullPath;
    }

    public function setResizeFileNameParams(array $fileParams) {
        $this->_resizeImageFileNameParams = $fileParams;
    }

    public function getResizeFileNameParams() {
        return $this->_resizeImageFileNameParams;
    }

    public function setCacheDir($path) {
        $this->_resizedImageCacheDir = '/' . trim($path, '/') . '/';
    }

    public function getCacheDir() {
        return is_null($this->_resizedImageCacheDir) ? self::DEFAULT_CACHE_PATH : $this->_resizedImageCacheDir;
    }

    public function setResizedImageDir($path) {
        $this->_resizedImageDirPath = trim($path, '/');
    }

    public function getResizedImageDir() {
        if (is_null($this->_resizedImageDirPath)) {
            $this->_resizedImageDirPath = $this->_convertHashInPath(md5($this->getOriginFullPath()));
        }
        return $this->_resizedImageDirPath;
    }

    public function setResizeImageName($name) {
        $this->_resizedImageFileName = trim($name, '/');
    }

    public function getResizeImageName() {
        if (is_null($this->_resizedImageFileName)) {
            if (empty($this->_resizeImageFileNameParams)) {
                throw new Exception('Set resize image file name params!');
            }
            $this->_resizedImageFileName = join('.', $this->_resizeImageFileNameParams);
        }
        return $this->_resizedImageFileName;
    }

    public function getResizedImagePath() {
        return $this->getCacheDir() . join('/', [
            $this->getResizedImageDir(),
            $this->getResizeImageName()
        ]);
    }

    public function getFullResizedImagePath() {
        return $this->_rootDir . $this->getResizedImagePath();
    }

    public function createDirForResizedImage() {
        $dir = join('', [$this->_rootDir, $this->getCacheDir(), $this->getResizedImageDir()]);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    private function _convertHashInPath($hash) {
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

    private function _checkImage() {
        if (!is_file($this->getOriginFullPath())) {
            throw new Exception('Wrong image path was given');
        }
        if (@getimagesize($this->getOriginFullPath()) === false) {
            throw new Exception('Wrong image file was given');
        }
    }

}